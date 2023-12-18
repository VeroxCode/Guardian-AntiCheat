<?php

namespace veroxcode\Guardian\Checks\Movement;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\math\Vector3;
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

class Fly extends Check
{

    public function __construct()
    {
        parent::__construct("Fly", CheckManager::MOVEMENT);
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();
        $eligibleGamemode = $player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE();

        if ($user->getTicksSinceJoin() < 40 || !$eligibleGamemode  || $player->isGliding() || $player->isSwimming()){
            return;
        }

        $OldY = $player->getPosition()->getY();
        $NewY = $packet->getPosition()->getY() - 1.62;
        $yMotion = $user->getServerMotion()->getY();

        if ($player->isOnGround()){
            $user->setWaitForGround(false);
            $yMotion = 0;
        }

        if ($user->getTicksSinceJump() >= 0 && $user->getTicksSinceJump() <= 5 && ($player->isOnGround() || $player->getWorld()->getBlock($player->getPosition()->add(0, $yMotion, 0))->isSolid())){
            $yMotion += Constants::GRAVITY * $user->getTicksSinceJump();
        }

        if (abs($user->getMotion()->getY()) >= 0){
            $yMotion += ($user->getMotion()->getY() * 3);
            $user->getMotion()->y = 0;
        }

        if (Blocks::isInsideLiquid($player) || Blocks::isOnClimbable($player) || Blocks::isInCobweb($player) || $user->getTicksSinceStep() < 80){
            $yMotion = 0;
            $user->getServerMotion()->y = $yMotion;
            return;
        }

        $effects = $player->getEffects();
        $jump_boost = $effects->get(VanillaEffects::JUMP_BOOST());
        $levitation = $effects->get(VanillaEffects::LEVITATION());
        $jump_boost = $jump_boost != null ? $jump_boost->getEffectLevel() : 0;

        if ($jump_boost > 0 || $levitation != null){
            $user->setWaitForGround(true);
            return;
        }

        if ($player->getWorld()->getBlock($player->getPosition()->add(0, $yMotion, 0))->isSolid() || $user->isWaitForGround()){
            return;
        }

        $CalcY = $OldY;
        $yMotion -= Constants::GRAVITY;
        $yMotion *= Constants::DRAG;
        $CalcY += $yMotion;

        $difference = abs($CalcY - $NewY);
        $user->getServerMotion()->y = $yMotion;
        $jumpTicks = $user->getTicksSinceJump();

        $tolerance = $user->getTicksSinceMotion() < 60 ? 1.5 : 0.5;
        $tolerance = Blocks::isOnSteppable($player) ? 0.9 : $tolerance;

        if ($NewY > $CalcY && $difference > $tolerance){
            //$player->sendMessage("New: $NewY | Calc: $CalcY \n Tolerance: $tolerance | Difference: $difference | JumpTicks | $jumpTicks");
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName(), 1.25);

                if ($this->getPunishment() == "Cancel"){
                    Punishments::punishPlayer($this, $user, $player->getPosition()->add(0, $yMotion, 0));
                }

            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($this, $user, $player->getPosition());
            }
        }else{
            $user->decreaseViolation($this->getName(), 0.5);
        }

    }
}