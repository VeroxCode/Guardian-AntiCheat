<?php

namespace veroxcode\Checks\Combat;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Checks\Check;
use veroxcode\Checks\Notifier;
use veroxcode\User\User;
use veroxcode\Utils\Rotations;

class ImpossibleRotations extends Check
{

    public function __construct()
    {
        parent::__construct("ImpossibleRotations");
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {

        $delta = abs($packet->getYaw() - $player->getLocation()->getYaw());
        $delta = Rotations::wrapAngleTo180_float($delta);

        if ($delta > 3.5){
            if ($packet->getHeadYaw() == $packet->getYaw()){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName());
                }
            }else{
                $user->decreaseViolation($this->getName(), 2);
            }
        }

        if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
            Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            $user->setPunishNext(true);
        }
    }

}