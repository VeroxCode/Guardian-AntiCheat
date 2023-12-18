<?php

namespace veroxcode\Guardian\Panel;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Guardian;

class AdminPanel
{

    /**
     * @param Player $player
     * @return void
     */
    public static function open(Player $player): void
    {
        $form = new SimpleForm(function($player, ?int $data = null){
            if ($data === null){
                return false;
            }

            switch ($data){
                case 0:
                    AdminPanel::listPlayers($player);
                    break;
                case 1:
                    self::openSettingsMenu($player);
                    break;
                case 2:
                    self::openOtherSettings($player);
                    break;
                default:
                    break;
            }

            return true;
        });

        $form->setTitle("Admin Panel");
        $form->addButton("Online Players");
        $form->addButton("Settings");
        $form->addButton("Other");

        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private static function listPlayers(Player $player): void
    {
        $onlinePlayers = Guardian::getInstance()->getServer()->getOnlinePlayers();
        $keys = array_keys($onlinePlayers);

        $form = new SimpleForm(function($formplayer, ?int $data = null) use ($onlinePlayers, $keys, $player){
            if ($data === null){
                return false;
            }

            if (isset($onlinePlayers[$keys[$data]])){
                $selected = $onlinePlayers[$keys[$data]];

                if ($selected instanceof Player){
                    self::createStats($player, $selected);
                }
            }

            return true;
        });

        foreach($onlinePlayers as $onlinePlayer) {
            $form->addButton($onlinePlayer->getName());
        }

        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param Player $statsPlayer
     * @return void
     */
    private static function createStats(Player $player, Player $statsPlayer): void
    {
        $statsName = $statsPlayer->getName();
        $statsUser = Guardian::getInstance()->getUserManager()->getUser($statsPlayer->getUniqueId()->toString());

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($statsUser){
            if ($data === null){
                return true;
            }
            return false;
        });

        $form->setTitle("$statsName Violations");

        foreach (Guardian::getInstance()->getCheckManager()->getChecks() as $check){
            $checkName = $check->getName();
            $checkViolations = $statsUser->getTotalViolation($checkName);
            $form->addLabel("§a$checkName: §r$checkViolations");
        }

        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openSettingsMenu(Player $player): void
    {
        $form = new SimpleForm(function($formplayer, ?int $data = null) use ($player){
            if ($data === null){
                return false;
            }

            switch ($data){
                case 0:
                    self::openCheckNotifications($player);
                    break;
                case 1:
                    self::openCheckToggle($player);
                    break;
                case 2:
                    self::openCheckAlertFrequency($player);
                    break;
                case 3:
                    self::openCheckMaxViolations($player);
                    break;
                case 4:
                    self::openCheckPunishments($player);
                    break;
                default:
                    break;
            }

            return true;
        });

        $form->setTitle("Settings Menu");
        $form->addButton("Check Notifications");
        $form->addButton("Check Activation");
        $form->addButton("Check AlertFrequency");
        $form->addButton("Check MaxViolations");
        $form->addButton("Check Punishments");
        $player->sendForm($form);

    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openOtherSettings(Player $player): void
    {
        $user = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
        $form = new CustomForm(function($formplayer, ?array $data = null) use ($player, $user){
            if ($data === null){
                return false;
            }

            $keys = array_keys($data);

            for ($i = 0; $i < count($data); $i++){
                switch ($i){
                    case 0:
                        $user->setNotifications($data[$keys[$i]] ?? false);
                        break;
                    case 1:
                        Guardian::getInstance()->getSavedConfig()->set("enable-debug", $data[$keys[$i]]);
                        break;
                    case 2:
                        Guardian::getInstance()->getSavedConfig()->set("prefix", $data[$keys[$i]]);
                        break;
                    case 3:
                        Guardian::getInstance()->getSavedConfig()->set("alert-message-debug", $data[$keys[$i]]);
                        break;
                    case 4:
                        Guardian::getInstance()->getSavedConfig()->set("alert-message", $data[$keys[$i]]);
                        break;
                    case 5:
                        Guardian::getInstance()->getSavedConfig()->set("kick-message", $data[$keys[$i]]);
                        break;
                    case 6:
                        Guardian::getInstance()->getSavedConfig()->set("ban-message", $data[$keys[$i]]);
                        break;
                    default:
                        break;
                }
            }
            return true;
        });

        $form->setTitle("Other Settings");
        $form->addToggle("Personal Alerts", $user->hasNotifications());
        $form->addToggle("Debug Mode (Global Setting)", Guardian::getInstance()->debugEnabled());
        $form->addInput("Prefix", "", Guardian::getInstance()->getSavedConfig()->get("prefix"));
        $form->addInput("Alert-Message-Debug", "", Guardian::getInstance()->getSavedConfig()->get("alert-message-debug"));
        $form->addInput("Alert-Message", "", Guardian::getInstance()->getSavedConfig()->get("alert-message"));
        $form->addInput("Kick-Message", "", Guardian::getInstance()->getSavedConfig()->get("kick-message"));
        $form->addInput("Ban-Message", "", Guardian::getInstance()->getSavedConfig()->get("ban-message"));
        $player->sendForm($form);

    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openCheckNotifications(Player $player): void
    {
        $checks = Guardian::getInstance()->getCheckManager()->getChecks();
        $keys = array_keys($checks);

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($checks, $keys, $player){
            if ($data === null){
                return false;
            }

            $keys2 = array_keys($data);
            for ($i = 0; $i < count($data) - 1; $i++){

                if (isset($checks[$keys[$i]])) {
                    $check = $checks[$keys[$i]];

                    if ($check instanceof Check) {
                        $check->setNotify($data[$keys2[$i]] ?? false);
                        Guardian::getInstance()->getSavedConfig()->set($check->getName() . "-notify", $data[$keys2[$i]] ?? false);
                    }
                }
            }

            return true;
        });

        foreach($checks as $check) {
            $form->addToggle($check->getName(), $check->hasNotify());
        }

        $form->setTitle("Check Notifications");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openCheckToggle(Player $player): void
    {
        $checks = Guardian::getInstance()->getCheckManager()->getChecks();
        $keys = array_keys($checks);

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($checks, $keys, $player){
            if ($data === null){
                return false;
            }

            $keys2 = array_keys($data);
            for ($i = 0; $i < count($data); $i++){

                if (isset($checks[$keys[$i]])) {
                    $check = $checks[$keys[$i]];

                    if ($check instanceof Check) {
                        $check->setEnabled($data[$keys2[$i]] ?? false);
                        Guardian::getInstance()->getSavedConfig()->set($check->getName() . "-enabled", $data[$keys2[$i]] ?? false);
                    }
                }
            }

            return true;
        });

        foreach($checks as $check) {
            $form->addToggle($check->getName(), $check->isEnabled());
        }

        $form->setTitle("Check Activation");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openCheckAlertFrequency(Player $player): void
    {
        $checks = Guardian::getInstance()->getCheckManager()->getChecks();
        $keys = array_keys($checks);

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($checks, $keys, $player){
            if ($data === null){
                return false;
            }

            $keys2 = array_keys($data);
            for ($i = 0; $i < count($data); $i++){

                if (isset($checks[$keys[$i]])) {
                    $check = $checks[$keys[$i]];

                    if ($check instanceof Check) {
                        $check->setAlertFrequency($data[$keys2[$i]] ?? 30);
                        Guardian::getInstance()->getSavedConfig()->set($check->getName() . "-AlertFrequency", $data[$keys2[$i]] ?? 25);
                    }
                }
            }

            return true;
        });

        foreach($checks as $check) {
            $form->addSlider($check->getName(), 1, 40, 1, $check->getAlertFrequency());
        }

        $form->setTitle("Check Activation");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openCheckMaxViolations(Player $player): void
    {
        $checks = Guardian::getInstance()->getCheckManager()->getChecks();
        $keys = array_keys($checks);

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($checks, $keys, $player){
            if ($data === null){
                return false;
            }

            $keys2 = array_keys($data);
            for ($i = 0; $i < count($data); $i++){

                if (isset($checks[$keys[$i]])) {
                    $check = $checks[$keys[$i]];

                    if ($check instanceof Check) {
                        $check->setMaxViolations($data[$keys2[$i]] ?? 30);
                        Guardian::getInstance()->getSavedConfig()->set($check->getName() . "-MaxViolations", $data[$keys2[$i]] ?? 25);
                    }
                }
            }

            return true;
        });

        foreach($checks as $check) {
            $form->addSlider($check->getName(), 1, 40, 1, $check->getMaxViolations());
        }

        $form->setTitle("Check Activation");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function openCheckPunishments(Player $player): void
    {
        $punishments = Guardian::getInstance()->getCheckManager()->getPunishments();
        $checks = Guardian::getInstance()->getCheckManager()->getChecks();
        $newChecks = [];
        $keys = array_keys($checks);

        foreach($checks as $check) {
            if ($check->getCategory() == CheckManager::MOVEMENT || $check->getCategory() == CheckManager::PLAYER){
                $newChecks[] = $check;
            }
        }

        $form = new CustomForm(function($formplayer, ?array $data = null) use ($newChecks, $keys, $player, $punishments){
            if ($data === null){
                return false;
            }

            $keys2 = array_keys($data);
            for ($i = 0; $i < count($data); $i++){

                if (isset($newChecks[$keys[$i]])) {
                    $check = $newChecks[$keys[$i]];

                    if ($check instanceof Check) {
                        $check->setPunishment($punishments[$data[$keys2[$i]]] ?? "Cancel");
                        Guardian::getInstance()->getSavedConfig()->set($check->getName() . "-Punishment", $punishments[$data[$keys2[$i]]] ?? "Cancel");
                    }
                }
            }

            return true;
        });

        foreach($checks as $check) {
            if ($check->getCategory() == CheckManager::MOVEMENT || $check->getCategory() == CheckManager::PLAYER){
                $form->addDropdown($check->getName(), $punishments, Guardian::getInstance()->getCheckManager()->getPunishmentID($check->getPunishment()));
            }
        }

        $form->setTitle("Check Activation");
        $player->sendForm($form);
    }

}