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
     * @param Check $check
     * @param User $user
     * @param Vector3|null $position
     * @param bool $reset
     * @return void
     */
    public static function punishPlayer(Check $check, User $user, ?Vector3 $position, bool $reset = true): void
    {

        $punishment = $check->getPunishment();
        $player = $user->getPlayer();

        if ($punishment == null){
            return;
        }

        switch ($punishment){
            case "Cancel":
                if ($position != null){
                    $player->teleport($position);
                    $user->handleCorrection($position);
                }
                break;
            case "Kick":
                if (Guardian::getInstance()->debugEnabled()){
                    self::SentTitleMessage($player, $check, " (Kick)");
                }else{
                    self::KickUser($player, $check);
                }
                break;
            case "Ban":
                if (Guardian::getInstance()->debugEnabled()){
                    self::SentTitleMessage($player, $check, " (Ban)");
                }else{
                    self::BanUser($player);
                }
                break;
        }
        if ($reset){
            $user->resetViolation($check->getName());
        }
    }

    public static function SentTitleMessage(Player $player, Check $check, string $extra): void
    {
        $config = Guardian::getInstance()->getSavedConfig();
        $prefix = $config->get("prefix");

        $player->sendTitle($prefix . " " . $check->getName() . $extra);
    }

    public static function KickUser(Player $player, Check $check): void
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