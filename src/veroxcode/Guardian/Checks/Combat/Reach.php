<?php

namespace veroxcode\Guardian\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Constants;

class Reach extends Check
{

    private float $MAX_REACH;

    public function __construct()
    {
        parent::__construct("Reach");

        $config = Guardian::getInstance()->getSavedConfig();
        $this->MAX_REACH = $config->get("Maximum-Reach") == null ? Constants::ATTACK_REACH : $config->get("Maximum-Reach");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player){

            $eligibleGamemode = $player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE();

            if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || !$eligibleGamemode){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = Guardian::getInstance()->getUserManager()->getUser($victimUUID);

            $rawplayerVec = new Vector3($player->getPosition()->getX(), 0 , $player->getPosition()->getZ());
            $rawvictimVec = new Vector3($victim->getPosition()->getX(), 0 , $victim->getPosition()->getZ());
            $rawdistance = $rawplayerVec->distance($rawvictimVec);

            if ($rawdistance <= $this->MAX_REACH){
                return;
            }

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 2;

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