<?php

namespace veroxcode\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use veroxcode\Checks\Check;
use veroxcode\Checks\Notifier;
use veroxcode\Guardian;
use veroxcode\User\User;
use veroxcode\Utils\Constants;
use veroxcode\Utils\Raycast;

class Reach extends Check
{

    private float $MAX_REACH;

    public function __construct()
    {
        parent::__construct("Reach");

        $config = Guardian::getInstance()->getConfig();
        $this->MAX_REACH = $config->get("Maximum-Reach") == null ? Constants::ATTACK_REACH : $config->get("Maximum-Reach");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player){

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = Guardian::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50);

            if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks){
                return;
            }

            $rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
            $playerVec = new Vector3($player->getPosition()->getX(), 0 , $player->getPosition()->getZ());
            $victimVec = new Vector3($rewindBuffer->getPosition()->getX(), 0 , $rewindBuffer->getPosition()->getZ());
            $distance = $playerVec->distance($victimVec);

            if ($distance > $this->MAX_REACH) {
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