<?php

namespace veroxcode\Checks;

use veroxcode\Guardian;
use veroxcode\User\User;
use veroxcode\Utils\Constants;

class Notifier
{

    /**
     * @param string $name
     * @param User $user
     * @param Check $Check
     * @param int $Violation
     * @param bool $notify
     * @return void
     */
    public static function NotifyFlag(string $name, User $user, Check $Check, int $Violation, bool $notify) : void
    {
        $config = Guardian::getInstance()->getConfig();
        $user->increaseAlertCount($Check->getName());

        if ($user->getAlertCount($Check->getName()) < Guardian::getInstance()->getConfig()->get($Check->getName() . "-AlertFrequency")){
            return;
        }

        if (!Guardian::getInstance()->getConfig()->get("enable-debug")){
            if ($notify){
                self::NotifyPlayers($name, $user, $Check);
            }
            return;
        }

        $message = $config->get("alert-message-debug");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check->getName(), $msgCheckPos, 7);
        $msgViolationPos = strpos($message, "%VIOLATION%");
        $message = substr_replace($message, $Violation, $msgViolationPos, 11);

        foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player){

            $notifyUser = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
            $hasNotifications = $notifyUser->hasNotifications();

            if ($hasNotifications){
                $player->sendMessage($message);
            }
        }
        $user->resetAlertCount($Check->getName());
    }

    /**
     * @param string $name
     * @param User $user
     * @param Check $Check
     * @return void
     */
    public static function NotifyPlayers(string $name, User $user, Check $Check) : void
    {

        $config = Guardian::getInstance()->getConfig();
        $message = $config->get("alert-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check->getName(), $msgCheckPos, 7);

        foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player){

            $notifyUser = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
            $hasNotifications = $notifyUser->hasNotifications();

            if ($player->hasPermission("guardian.notify")){
                if ($hasNotifications){
                    $player->sendMessage($message);
                }
            }
        }
        $user->resetAlertCount($Check->getName());
    }

}