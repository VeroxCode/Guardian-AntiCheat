<?php

namespace veroxcode;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use veroxcode\Checks\CheckManager;
use veroxcode\Listener\EventListener;
use veroxcode\User\UserManager;
use veroxcode\Utils\Constants;

class Guardian extends PluginBase implements \pocketmine\event\Listener
{

    private static Guardian $instance;

    public UserManager $userManager;
    public CheckManager $checkManager;


    public function onEnable() : void
    {
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->userManager = new UserManager();
        $this->checkManager = new CheckManager();

    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     * @throws JsonException
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
   {

       if ($command->getName() == "guardian") {
           if (isset($args[0])) {
               if ($args[0] == "help"){
                   $sender->sendMessage(
                       Constants::PREFIX . "help.\n"
                       .   Constants::PREFIX . "debug.\n"
                       .   Constants::PREFIX . "notify <Check>.");
                       $this->getConfig()->save();
                       return true;
                   }

               if ($args[0] == "debug"){
                   $debug = $this->getConfig()->get("enable-debug");
                   $this->getConfig()->set("enable-debug", !$debug);
                   $sender->sendMessage(Constants::PREFIX . "Done.");
                   $this->getConfig()->save();
                   return true;
               }

               if ($args[0] == "notify"){
                   if (!isset($args[1])) {
                       return false;
                   }

                   $newnotify = $this->getConfig()->get($args[1] . "-notify");
                   if ($this->getCheckManager()->getCheckByName($args[1]) != null){
                       $this->getCheckManager()->getCheckByName($args[1])->setNotify(!$newnotify);
                       $this->getConfig()->set($args[1] . "-notify", !$newnotify);
                       $sender->sendMessage(Constants::PREFIX . "Done.");
                       $this->getConfig()->save();
                       return true;
                   }
               }
           }
       }
       return false;
   }

    /**
     * @return Guardian
     */
    public static function getInstance() : Guardian
   {
       return self::$instance;
   }

    /**
     * @return CheckManager
     */
    public function getCheckManager() : CheckManager
   {
       return $this->checkManager;
   }

    /**
     * @return UserManager
     */
    public function getUserManager() : UserManager
    {
        return $this->userManager;
    }

}