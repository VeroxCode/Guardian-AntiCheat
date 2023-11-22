<?php

namespace veroxcode\Guardian\Checks\Packets;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\User\User;

class BadPacketsB extends Check
{

    public function __construct()
    {
        parent::__construct("BadPacketsB");
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();

        if ($user->getTicksSinceJoin() > 40){
            $rewindFrame = $user->rewindMovementBuffer();

            if ($packet->getTick() < $rewindFrame->getPlayerTick()){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }

                if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                    Punishments::punishPlayer($this, $user, $player->getPosition());
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                }
            }

        }

    }
}