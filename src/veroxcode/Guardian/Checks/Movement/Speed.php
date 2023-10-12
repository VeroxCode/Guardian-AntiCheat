<?php

namespace veroxcode\Guardian\Checks\Movement;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Constants;
use veroxcode\Guardian\Utils\Random;

class Speed extends Check
{

    public function __construct()
    {
        parent::__construct("Speed");
    }

    public function onMotion(EntityMotionEvent $event, User $user): void
    {
        $user->setMotion($event->getVector());
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {

        $eligibleGamemode = $player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE();

        if (!$eligibleGamemode){
            return;
        }

        $previous = $player->getPosition();
        $next = $packet->getPosition();

        $previous = new Vector3($previous->x, 0, $previous->z);
        $next = new Vector3($next->x, 0, $next->z);

        $frictionBlock = $player->getWorld()->getBlock($player->getPosition()->getSide(Facing::DOWN));
        $friction = $player->isOnGround() ? $frictionBlock->getFrictionFactor() : 1.0;
        $lastDistance = $user->getLastDistanceXZ();
        $momentum = self::getMomentum($lastDistance, $friction);
        $movement = self::getMovement($player, new Vector3($user->getMoveForward(), 0, $user->getMoveStrafe()));
        $effects = self::getEffectsMultiplier($player);
        $acceleration = self::getAcceleration($movement, $effects, $friction, $player->isOnGround());

        $expected = $momentum + $acceleration;
        $expected = Random::clamp(0.1, PHP_FLOAT_MAX, $expected);

       if ($user->hasMotion()){
            $motion = $user->getMotion();
            $knockback = $motion->length();

            $knockback *= 3.5;
            $expected += $knockback;

            $user->setMotion(Vector3::zero());
        }

        $user->setLastDistanceXZ($expected);
        $expected += ($player->isOnGround() && $user->getTicksSinceLanding() < 10) ? 0.35 : 0;
        $expected += ($user->getTicksSinceJump() <= 20 && $user->getTicksSinceIce() <= 20) ? 0.2 : 0;
        $expected += $user->getTicksSinceJump() <= 10 ? 0.1 : 0;
        $expected += $user->getTicksSinceJump() <= 3 ? 0.3 : 0;

        $dist = $previous->distance($next);

        if ($dist > $expected){
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName());
            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
            }
        }else{
            $user->decreaseViolation($this->getName(), 0.5);
        }

    }

    public function getMotionMovement(User $user, float $yaw, Vector3 $previous) : Vector3
    {
        $forward = $user->getMoveForward();
        $strafe = $user->getMoveStrafe();

        $X = -sin($yaw) * Constants::BLOCKS_PER_TICK;
        $Z = cos($yaw) * Constants::BLOCKS_PER_TICK;

        $X *= $forward;
        $Z *= $strafe;

        return new Vector3($previous->x + $X, $previous->y, $previous->z + $Z);
    }

    public function getMovement(Player $player, Vector3 $move): float
    {
        $movement = 1.0;

        if ($player->isSprinting()){
            $movement = 1.3;
        }

        if ($player->isSneaking()){
            $movement = 0.3;
        }

        if ($player->isUsingItem()){
            $movement = 0.2;
        }

        return $movement;
    }

    public function getEffectsMultiplier(Player $player) : float
    {
        $effects = $player->getEffects();
        $speed = $effects->get(VanillaEffects::SPEED());
        $slowness = $effects->get(VanillaEffects::SLOWNESS());

        $speed = $speed != null ? $speed->getEffectLevel() : 0;
        $slowness = $slowness != null ? $slowness->getEffectLevel() : 0;

        return (1 + 0.2 * $speed) * (1 - 0.15 * $slowness);
    }

    public function getMomentum(float $lastDistance, float $friction) : float
    {
        return $lastDistance * $friction * 0.91;
    }

    public function getAcceleration(float $movement, float $effectMultiplier, float $friction, bool $onGround) : float
    {
        if (!$onGround){
            return 0.02 * $movement;
        }

        return 0.1 * $movement * $effectMultiplier * ((0.6 / $friction) ** 3);
    }

}

