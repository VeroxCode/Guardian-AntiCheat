<?php

namespace veroxcode\Checks\World;

use pocketmine\block\Bed;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use veroxcode\Checks\Check;
use veroxcode\Checks\Notifier;
use veroxcode\Checks\Punishments;
use veroxcode\User\User;
use veroxcode\Utils\Raycast;

class GhostHand extends Check
{

    public function __construct()
    {
        parent::__construct("GhostHand");
    }

    public function onBlockBreak(BlockBreakEvent $event, User $user): void
    {

        if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN){
            return;
        }

        $block = $event->getBlock();
        $player = $event->getPlayer();
        $distance = $player->getPosition()->distance($block->getPosition());
        $rayBlock = Raycast::getBlockOnLine($player, $player->getPosition(), $player->getDirectionVector(), $distance);

        if ($rayBlock != null){
            if (str_contains(strtolower($block->getName()), "grass") && !$block->isFullCube() || str_contains(strtolower($block->getName()), "layer") && !$block->isFullCube()){
                return;
            }

            if ($block->isTransparent()){
                if (!($block instanceof Bed || $block instanceof Glass || $block instanceof Chest)) {
                    return;
                }
            }

            if ($rayBlock !== $block){
                $event->cancel();
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }
            }else{
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                if ($this->getPunishment() != "Cancel") {
                    Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
                }
            }


        }
    }

}