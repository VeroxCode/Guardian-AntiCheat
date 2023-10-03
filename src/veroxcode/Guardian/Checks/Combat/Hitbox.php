<?php

namespace veroxcode\Guardian\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Raycast;

class Hitbox extends Check
{

    public function __construct()
    {
        parent::__construct("Hitbox");
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player){

            if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN){
                return;
            }

            $ray = Raycast::isBBOnLine($victim->getBoundingBox(), $player->getPosition(), $player->getDirectionVector(), $player->getPosition()->distance($victim->getPosition()));
            if ($ray){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = Guardian::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 1;

            if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks){
                return;
            }

            $rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
            $rewindray = Raycast::isBBOnLine($rewindBuffer->getBoundingBox(), $player->getPosition(), $player->getDirectionVector(), $player->getPosition()->distance($victim->getPosition()));

            if (!$rewindray){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName(), 2);
                }
            }else{
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                $event->cancel();
            }
        }
    }

}