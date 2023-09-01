<?php

namespace veroxcode\Checks;

use veroxcode\Guardian;

class Notifier
{

    /**
     * @param string $name
     * @param string $Check
     * @param int $Violation
     * @return void
     */
    public static function NotifyFlag(string $name, string $Check, int $Violation) : void
    {

        if (!Guardian::getInstance()->getConfig()->get("enable-debug")){
            return;
        }

        foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player){
            $player->sendMessage("§e[Guardian] §c" . $name . "§f failed §a" . $Check . " §a[§4" . $Violation . "§a]");
        }
    }

}