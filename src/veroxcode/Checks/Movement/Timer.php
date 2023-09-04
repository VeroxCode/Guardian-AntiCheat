<?php

namespace veroxcode\Checks\Movement;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Checks\Check;
use veroxcode\Checks\Notifier;
use veroxcode\Checks\Punishments;
use veroxcode\Guardian;
use veroxcode\User\User;

class Timer extends Check
{

    private int $MAX_TICK_DIFFERENCE;

    public function __construct()
    {
        parent::__construct("Timer");

        $config = Guardian::getInstance()->getConfig();
        $this->MAX_TICK_DIFFERENCE = $config->get("Timer-TickDifference") == null ? 10 : $config->get("Timer-TickDifference");

    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {
        $newTickDelay = Guardian::getInstance()->getServer()->getTick() - $packet->getTick();
        $delayDifference = $user->getTickDelay() - $newTickDelay;

        if ($delayDifference >= $this->MAX_TICK_DIFFERENCE){
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName(), 1);
            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
                $user->setTickDelay($newTickDelay);
            }
        }else{
            $user->decreaseViolation($this->getName(), 1);
        }
    }
}