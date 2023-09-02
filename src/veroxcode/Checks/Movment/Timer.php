<?php

namespace veroxcode\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Checks\Check;
use veroxcode\Checks\Notifier;
use veroxcode\Guardian;
use veroxcode\User\User;
use veroxcode\Utils\Constants;
use veroxcode\Utils\Raycast;

class Timer extends Check
{

    public function __construct()
    {
        parent::__construct("Timer", 10);
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {
        $player->sendMessage($user->getTickDelay());
        $newTickDelay = Guardian::getInstance()->getServer()->getTick() - $packet->getTick();
        $player->sendMessage($newTickDelay);
    }

}