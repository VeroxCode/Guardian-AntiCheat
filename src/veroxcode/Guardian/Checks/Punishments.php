<?php

namespace veroxcode\Guardian\Checks;

use pocketmine\math\Vector3;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;

class Punishments
{

    /**
     * @param Player $player
     * @param Check $check
     * @param User $user
     * @param Vector3|null $position
     * @param string|null $punishment
     * @param bool $reset
     * @return void
     */
    public static function punishPlayer(Player $player, Check $check, User $user, ?Vector3 $position, ?string $punishment, bool $reset = true): void
    {
        if ($punishment == null){
            return;
        }

        switch ($punishment){
            case "Cancel":
                if ($position != null){
                    $player->teleport($position);
                    if ($reset) $user->resetViolation($check->getName());
                }
                break;
            case "Kick":
                self::KickUser($player);
                break;
            case "Ban":
                self::BanUser($player);
                break;
        }
    }

    public static function KickUser(Player $player): void
    {
        $config = Guardian::getInstance()->getSavedConfig();
        $message = $config->get("kick-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);

        $player->kick($message);
    }

    public static function BanUser(Player $player): void
    {

        $config = Guardian::getInstance()->getSavedConfig();
        $message = $config->get("ban-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);

        $Ban = new BanEntry($player->getName());
        $Ban->setReason($message);
        Guardian::getInstance()->getServer()->getNameBans()->add($Ban);
        $player->kick($message);
    }

}