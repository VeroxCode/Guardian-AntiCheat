<?php

namespace veroxcode\Guardian\Checks\Movement;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;

class Timer extends Check
{

    private int $MAX_TICK_DIFFERENCE;

    public function __construct()
    {
        parent::__construct("Timer", CheckManager::MOVEMENT);

        $config = Guardian::getInstance()->getSavedConfig();
        $this->MAX_TICK_DIFFERENCE = $config->get("Timer-TickDifference") == null ? 10 : $config->get("Timer-TickDifference");

    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();

        $serverTps = Guardian::getInstance()->getServer()->getTicksPerSecond();
        $serverTick = Guardian::getInstance()->getServer()->getTick();
        $newTickDelay = $serverTick - $packet->getTick();
        $delayDifference = $user->getTickDelay() - $newTickDelay;

        if ($user->getTicksSinceJoin() < 200 || $serverTps < 19){
            $serverTick = Guardian::getInstance()->getServer()->getTick();
            $newTickDelay = $serverTick - $packet->getTick();
            $user->setTickDelay($newTickDelay);
            return;
        }

        if ((float) $delayDifference >= ($this->MAX_TICK_DIFFERENCE + (abs(20 - $serverTps) * 2))){
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName(), 1);
            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($this, $user, $player->getPosition());
                $user->setTickDelay($newTickDelay);
            }
        }else{
            $user->decreaseViolation($this->getName(), 1);
        }
    }
}