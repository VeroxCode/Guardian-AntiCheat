<?php

namespace veroxcode\Guardian\Checks\Packets;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\User\User;

class BadPacketsA extends Check
{

    public function __construct()
    {
        parent::__construct("BadPacketsA");
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {
        foreach ($user->getMovementBuffer() as $moveFrame){
            if ($moveFrame->getPlayerTick() == $packet->getTick()){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName());
                }else{
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                    Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
                }
            }
        }
    }
}