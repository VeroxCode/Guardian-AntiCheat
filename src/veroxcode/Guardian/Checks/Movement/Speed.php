<?php

namespace veroxcode\Guardian\Checks\Movement;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CorrectPlayerMovePredictionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Blocks;
use veroxcode\Guardian\Utils\Constants;
use veroxcode\Guardian\Utils\Random;

class Speed extends Check
{

    public function __construct()
    {
        parent::__construct("Speed", CheckManager::MOVEMENT);
    }

    public function onMotion(EntityMotionEvent $event, User $user): void
    {
        $user->getMotion()->x += abs($event->getVector()->getX());
        $user->getMotion()->z += abs($event->getVector()->getZ());
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();
        $eligibleGamemode = $player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE();

        if (!$eligibleGamemode || ($player->isGliding() && $player->getArmorInventory()->getChestplate()->getTypeId() === ItemTypeIds::ELYTRA) || $player->isFlying() || $user->getTicksSinceCorrection() <= 2 || $user->getTicksSinceJoin() < 40){
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

        if (abs($user->getMotion()->getX()) > 0 || abs($user->getMotion()->getZ()) > 0){
            $motionX = abs($user->getMotion()->getX());
            $motionZ = abs($user->getMotion()->getZ());
            $knockback = $motionX * $motionX + $motionZ * $motionZ;

            $knockback *= 4;
            $expected += $knockback;

            $user->getMotion()->x = 0;
            $user->getMotion()->z = 0;
       }

        $expected += ($user->getTicksSinceJump() < 5 && Blocks::hasBlocksAbove($player)) ? 0.8 : 0;
        $expected += $user->getTicksSinceStep() < 5 ? 0.9 : 0;

        $user->setLastDistanceXZ($expected);

        $expected += ($player->isOnGround() && $user->getTicksSinceLanding() < 10) ? 0.4 : 0;
        $expected += ($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING) && $user->getTicksSinceLanding() > 5) ? 0.26 : 0;
        $expected += ($user->getTicksSinceJump() <= 20 && $user->getTicksSinceIce() <= 20) ? 0.2 : 0;

        $dist = $previous->distance($next);
        $distDiff = abs($dist - $expected);

        if ($dist > $expected && $distDiff > Constants::SPEED_THRESHOLD){
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName());

                if ($this->getPunishment() == "Cancel"){
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                    Punishments::punishPlayer($this, $user, $player->getPosition(), false);
                }else{
                    if ($player->isUsingItem()){
                        $player->teleport($player->getPosition());
                        $user->handleCorrection($player->getPosition());
                    }
                }

            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($this, $user, $player->getPosition());
            }
        }else{
            $user->decreaseViolation($this->getName(), 0.5);
        }
    }

    public function getMovement(Player $player, Vector3 $move): float
    {
        $armorLeggings = $player->getArmorInventory()->getLeggings();
        $movement = 1.0;

        if ($player->isSprinting()){
            $movement = 1.3;
        }

        if ($player->isSneaking()){
            $movement = Random::clamp(0.3, 1.0, 0.3 + (0.15 * $armorLeggings->getEnchantmentLevel(VanillaEnchantments::SWIFT_SNEAK())));
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

