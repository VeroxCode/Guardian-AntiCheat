<?php

namespace veroxcode\Checks;

use veroxcode\Guardian;
use veroxcode\Utils\Constants;

class Notifier
{

    /**
     * @param string $name
     * @param string $Check
     * @param int $Violation
     * @return void
     */
    public static function NotifyFlag(string $name, string $Check, int $Violation, bool $notify) : void
    {
        if (!Guardian::getInstance()->getConfig()->get("enable-debug") || !$notify){
            self::NotifyPlayers($name, $Check);
            return;
        }

        $config = Guardian::getInstance()->getConfig();
        $message = $config->get("alert-message-debug");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check, $msgCheckPos, 7);
        $msgViolationPos = strpos($message, "%VIOLATION%");
        $message = substr_replace($message, $Violation, $msgViolationPos, 11);

        foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player){
            $player->sendMessage($message);
        }
    }

    public static function NotifyPlayers(string $name, string $Check) : void
    {

        $config = Guardian::getInstance()->getConfig();
        $message = $config->get("alert-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check, $msgCheckPos, 7);

        foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player){
            if ($player->hasPermission("guardian.notify")){
                $player->sendMessage($message);
            }
        }
    }

}