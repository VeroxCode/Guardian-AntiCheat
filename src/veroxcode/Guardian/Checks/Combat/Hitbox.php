<?php

namespace veroxcode\Guardian\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Constants;
use veroxcode\Guardian\Utils\Raycast;

class Hitbox extends Check
{

    public function __construct()
    {
        parent::__construct("Hitbox", CheckManager::COMBAT);
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $user->getPlayer();
        $victim = $event->getEntity();
        $distance = $player->getPosition()->distance($victim->getPosition());

        if ($victim instanceof Player){

            if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN || $event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                return;
            }

            $ray = Raycast::isBBOnLine($victim->getPosition(), $player->getPosition(), $player->getDirectionVector(), 6);
            if ($ray){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = Guardian::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 3;

            if ($user->getTicksSinceJoin() < 40 || count($user->getMovementBuffer()) <= $rewindTicks){
                return;
            }

            for ($i = 0; $i < $rewindTicks; $i++) {
                $rewindVictim = $victimUser->rewindMovementBuffer($i);
                $rewindVec = Raycast::isBBOnLine($rewindVictim->getPosition(), $player->getPosition(), $player->getDirectionVector(), 6);

                if ($rewindVec) {
                    $user->decreaseViolation($this->getName());
                    return;
                }
            }

            if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                $user->increaseViolation($this->getName(), 2);
            }

            $event->cancel();

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            }
        }
    }

}