<?php

namespace veroxcode\Guardian\User;

use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\Player;
use veroxcode\Guardian\Buffers\AttackFrame;
use veroxcode\Guardian\Buffers\MovementFrame;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\Utils\Blocks;
use veroxcode\Guardian\Utils\Random;

class User
{

    private CONST MOVEMENT_BUFFER_SIZE = 100;
    private CONST ATTACK_BUFFER_SIZE = 100;

    private Vector3 $motion;
    private Vector3 $serverPosition;
    private Vector3 $serverMotion;
    private Vector3 $moveDelta;

    private Player $player;
    private string $uuid;

    private bool $WaitForGround = true;
    private bool $notifications = true;
    private bool $punishNext = false;

    private float $moveForward = 0.0;
    private float $moveStrafe = 0.0;
    private float $lastDistanceXZ = 0.28;
    private float $yMotion = 0.0;
    private float $lastJumpingHeight = 0.0;
    private float $lastGround = 0.0;

    private int $ticksSinceCorrection = 0;
    private int $ticksSinceLanding = 0;
    private int $ticksSinceMotion = 0;
    private int $ticksSinceJump = 0;
    private int $ticksSinceJoin = 0;
    private int $ticksSinceStep = 0;
    private int $ticksSinceIce = 0;
    private int $ticksInAir = 0;

    private int $lastKnockbackTick = 0;
    private int $firstServerTick = 0;
    private int $firstClientTick = 0;
    private int $lastAttack = 0;
    private int $tickDelay = 0;
    private int $input = 0;

    private array $movementBuffer = [];
    private array $attackBuffer = [];
    private array $violations = [];
    private array $alerts = [];
    private array $cache = [];

    /**
     * @param Player $player
     * @param string $uuid
     */
    public function __construct(Player $player, string $uuid)
    {
        $this->player = $player;
        $this->uuid = $uuid;
        $this->motion = Vector3::zero();
        $this->moveDelta = Vector3::zero();
        $this->serverPosition = $player->getPosition();
        $this->serverMotion = Vector3::zero();
        $this->lastGround = $player->getPosition()->getY();

        $config = Guardian::getInstance()->getSavedConfig();
        foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $Check){

            $frequency = $config->get($Check->getName() . "-AlertFrequency");

            $this->violations[$Check->getName()] = 0.0;
            $this->alerts[$Check->getName()] = $frequency;
        }
    }

    public function preMove(PlayerAuthInputPacket $packet, Player $player): void
    {
        $moveForward = Random::clamp(-1, 1, $packet->getMoveVecZ());
        $moveStrafe = Random::clamp(-1, 1, $packet->getMoveVecX());

        $this->player = $player;
        $this->setMoveForward($moveForward);
        $this->setMoveStrafe($moveStrafe);

        if ($player->isOnGround()){
            $this->lastGround = $packet->getPosition()->getY();
            $this->ticksInAir = 0;
            $this->ticksSinceLanding++;
        }else{
            $this->ticksInAir++;
            $this->ticksSinceLanding = 0;
        }

        $blockBelow = $player->getWorld()->getBlock($player->getPosition()->getSide(Facing::DOWN));
        if (Blocks::hasIceBelow($blockBelow)){
            $this->ticksSinceIce = 0;
        }else{
            $this->ticksSinceIce++;
        }

        if (Blocks::isOnSteppable($this->player)){
            $this->ticksSinceStep = 0;
        }

        $this->ticksSinceJump++;
        $this->ticksSinceJoin++;
        $this->ticksSinceStep++;
        $this->ticksSinceMotion++;
        $this->ticksSinceCorrection++;

        if ($this->getFirstClientTick() == 0 && $this->getFirstServerTick() == 0){
            $this->setFirstServerTick(Guardian::getInstance()->getServer()->getTick());
            $this->setFirstClientTick($packet->getTick());
            $this->setTickDelay(Guardian::getInstance()->getServer()->getTick() - $packet->getTick());
        }

        if ($this->getInput() == 0){
            $this->setInput($packet->getInputMode());
        }

    }

    public function postMove(PlayerAuthInputPacket $packet, Player $player): void
    {
        if ($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING)){
            $this->ticksSinceJump = 0;
            $this->lastJumpingHeight = $packet->getPosition()->getY();
        }
    }

    public function handleCorrection(Vector3 $position): void
    {
        $this->ticksSinceCorrection = 0;
    }

    public function getUUID(): string
    {
        return $this->uuid;
    }

    public function addToMovementBuffer(MovementFrame $object): void
    {
        $size = count($this->movementBuffer);

        if ($size >= ($this::MOVEMENT_BUFFER_SIZE)){
            array_shift($this->movementBuffer);
            $this->movementBuffer[$size - 1] = $object;
            return;
        }
        $this->movementBuffer[$size] = $object;
    }

    public function rewindMovementBuffer(int $ticks = 0): MovementFrame
    {

        $size = count($this->movementBuffer) - 1;
        $ticks = Random::clamp(0, PHP_INT_MAX, $size - $ticks);
        return $this->movementBuffer[$ticks];
    }

    public function getMovementBuffer(): array
    {
        return $this->movementBuffer;
    }

    public function addToAttackBuffer(AttackFrame $object): void
    {
        $size = count($this->attackBuffer);

        if ($size >= ($this::ATTACK_BUFFER_SIZE)){
            array_shift($this->attackBuffer);
            $this->attackBuffer[$size - 1] = $object;
            return;
        }
        $this->attackBuffer[$size] = $object;
    }

    public function rewindAttackBuffer(int $ticks = 1): AttackFrame
    {
        $ticks = Random::clamp(0, PHP_INT_MAX, $ticks);
        $size = count($this->attackBuffer) - 1;
        return $this->attackBuffer[$size - $ticks];
    }

    public function getAttackBuffer(): array
    {
        return $this->attackBuffer;
    }

    public function increaseViolation(string $Check, $amount = 1): void
    {
        $this->violations[$Check] = Random::clamp(0, PHP_INT_MAX, $this->violations[$Check] + $amount);
    }

    public function decreaseViolation(string $Check, $amount = 1): void
    {
        $this->violations[$Check] = Random::clamp(0, PHP_INT_MAX, $this->violations[$Check] - $amount);
    }

    public function resetViolation(string $Check): void
    {
        $this->violations[$Check] = 0;
    }

    public function getViolation(string $Check) : float
    {
        return $this->violations[$Check];
    }

    public function increaseAlertCount(string $Check, $amount = 1): void
    {
        $this->alerts[$Check] = Random::clamp(0, PHP_FLOAT_MAX, $this->alerts[$Check] + $amount);
    }

    public function decreaseAlertCount(string $Check, $amount = 1): void
    {
        $this->alerts[$Check] = Random::clamp(0, PHP_FLOAT_MAX, $this->alerts[$Check] - $amount);
    }

    public function resetAlertCount(string $Check): void
    {
        $this->alerts[$Check] = 0;
    }

    public function getAlertCount(string $Check): int
    {
        return $this->alerts[$Check];
    }

    public function getFirstServerTick(): int
    {
        return $this->firstServerTick;
    }

    public function setFirstServerTick(int $firstServerTick): void
    {
        $this->firstServerTick = $firstServerTick;
    }

    public function getFirstClientTick(): int
    {
        return $this->firstClientTick;
    }

    public function setFirstClientTick(int $firstClientTick): void
    {
        $this->firstClientTick = $firstClientTick;
    }

    public function getTickDelay(): int
    {
        return $this->tickDelay;
    }

    public function setTickDelay(int $tickDelay): void
    {
        $this->tickDelay = $tickDelay;
    }

    public function getInput(): int
    {
        return $this->input;
    }

    public function setInput(int $input): void
    {
        $this->input = $input;
    }

    public function getLastAttack(): int
    {
        return $this->lastAttack;
    }

    public function setLastAttack(int $lastAttack): void
    {
        $this->lastAttack = $lastAttack;
    }

    public function hasNotifications(): bool
    {
        return $this->notifications;
    }

    public function setNotifications(bool $notifications): void
    {
        $this->notifications = $notifications;
    }

    public function getLastKnockbackTick(): int
    {
        return $this->lastKnockbackTick;
    }

    public function setLastKnockbackTick(int $lastKnockbackTick): void
    {
        $this->lastKnockbackTick = $lastKnockbackTick;
    }

    public function hasMotion() : bool
    {
        return $this->motion->length() != 0;
    }

    public function getMotion(): Vector3
    {
        return $this->motion;
    }

    public function setMotion(Vector3 $motion): void
    {
        $this->motion = $motion;
    }

    public function getMoveForward(): float
    {
        return $this->moveForward;
    }

    public function setMoveForward(float $moveForward): void
    {
        $this->moveForward = $moveForward;
    }

    public function getMoveStrafe(): float
    {
        return $this->moveStrafe;
    }

    public function setMoveStrafe(float $moveStrafe): void
    {
        $this->moveStrafe = $moveStrafe;
    }

    public function isPunishNext(): bool
    {
        return $this->punishNext;
    }

    public function setPunishNext(bool $punishNext): void
    {
        $this->punishNext = $punishNext;
    }

    public function getMoveDelta(): Vector3
    {
        return $this->moveDelta;
    }

    public function setMoveDelta(Vector3 $moveDelta): void
    {
        $this->moveDelta = $moveDelta;
    }

    public function getLastDistanceXZ(): float
    {
        return $this->lastDistanceXZ;
    }

    public function setLastDistanceXZ(float $lastDistanceXZ): void
    {
        $this->lastDistanceXZ = $lastDistanceXZ;
    }

    public function getTicksSinceJump(): int
    {
        return $this->ticksSinceJump;
    }

    public function getTicksSinceLanding(): int
    {
        return $this->ticksSinceLanding;
    }

    public function getTicksSinceIce(): int
    {
        return $this->ticksSinceIce;
    }

    public function getYMotion(): float
    {
        return $this->yMotion;
    }

    public function setYMotion(float $yMotion): void
    {
        $this->yMotion = $yMotion;
    }

    public function addYMotion(float $yMotion): void
    {
        $this->yMotion += $yMotion;
    }

    public function getServerPosition(): Vector3
    {
        return $this->serverPosition;
    }

    public function setServerPosition(Vector3 $serverPosition): void
    {
        $this->serverPosition = $serverPosition;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getServerMotion(): Vector3
    {
        return $this->serverMotion;
    }

    public function setServerMotion(Vector3 $serverMotion): void
    {
        $this->serverMotion = $serverMotion;
    }

    public function getTicksInAir(): int
    {
        return $this->ticksInAir;
    }

    public function getTicksSinceJoin(): int
    {
        return $this->ticksSinceJoin;
    }

    public function getLastJumpingHeight(): float
    {
        return $this->lastJumpingHeight;
    }

    public function setLastJumpingHeight(float $lastJumpingHeight): void
    {
        $this->lastJumpingHeight = $lastJumpingHeight;
    }

    public function getLastGround(): float
    {
        return $this->lastGround;
    }

    public function setLastGround(float $lastGround): void
    {
        $this->lastGround = $lastGround;
    }

    public function getTicksSinceMotion(): int
    {
        return $this->ticksSinceMotion;
    }

    public function resetTicksSinceMotion(): void
    {
        $this->ticksSinceMotion = 0;
    }

    public function getTicksSinceCorrection(): int
    {
        return $this->ticksSinceCorrection;
    }

    public function getTicksSinceStep(): int
    {
        return $this->ticksSinceStep;
    }

    public function isWaitForGround(): bool
    {
        return $this->WaitForGround;
    }

    public function setWaitForGround(bool $WaitForGround): void
    {
        $this->WaitForGround = $WaitForGround;
    }

    public function getCache(): array
    {
        return $this->cache;
    }

    public function setCache(array $cache): void
    {
        $this->cache = $cache;
    }

}