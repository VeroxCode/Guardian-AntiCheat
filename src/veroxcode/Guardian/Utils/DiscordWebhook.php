<?php

namespace veroxcode\Guardian\Utils;

use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Guardian;

class DiscordWebhook
{

    public static function TestNotification(): void
    {
        if (!Guardian::getInstance()->WebhookEnabled()){
            Guardian::getInstance()->getLogger()->warning("Webhook Disabled");
            return;
        }

        $data = array('content' => 'Webhook TEST');
        $data = json_encode($data);

        $curl = curl_init(self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_URL, self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_exec($curl);

        if (curl_error($curl)) {
            Guardian::getInstance()->getLogger()->warning(curl_error($curl));
        }
    }

    public static function PostNotification(Player $player, Check $check): void
    {
        if (!Guardian::getInstance()->WebhookEnabled()){
            return;
        }

        $message = self::BuildMessage($player, $check);
        $data = array('content' => $message);
        $data = json_encode($data);

        $curl = curl_init(self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_URL, self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_exec($curl);

        if (curl_error($curl)) {
            Guardian::getInstance()->getLogger()->warning(curl_error($curl));
        }
    }

    private static function BuildMessage(Player $player, Check $check) : string
    {
        $config = Guardian::getInstance()->getSavedConfig();
        $message = $config->get("webhook-message");

        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $player->getName(), $msgPlayerPos,8);

        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $check->getName(), $msgCheckPos, 7);

        return $message;
    }

    private static function GetWebhookURL() : string
    {
        $config = Guardian::getInstance()->getSavedConfig();
        return $config->get("webhook-url");
    }

}