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

class AutoClicker extends Check
{

    private float $CPS_LIMIT;

    public function __construct()
    {
        parent::__construct("AutoClicker");

        $config = Guardian::getInstance()->getConfig();
        $this->CPS_LIMIT = $config->get("CPS-Limit") == null ? Constants::CPS_LIMIT : $config->get("CPS-Limit");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();

        if ($player instanceof Player){

            $hits = 0;

            foreach ($user->getAttackBuffer() as $attackFrame){
                if ((Guardian::getInstance()->getServer()->getTick() - ($attackFrame->getServerTick() - floor($attackFrame->getPing() / 50))) < Guardian::getInstance()->getServer()->getTicksPerSecond()){
                    $hits++;
                }
            }

            if ($hits >= $this->CPS_LIMIT){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName(), 1);
                }
            }else{
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $this->getName(), $user->getViolation($this->getName()), $this->hasNotify());
                $event->cancel();
            }
        }
    }

}