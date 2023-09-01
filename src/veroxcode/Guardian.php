<?php

namespace veroxcode;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use veroxcode\Checks\CheckManager;
use veroxcode\Listener\EventListener;
use veroxcode\User\UserManager;

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
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
   {

       if ($command->getName() == "test") {
           if (isset($args[0])) {
               $sender->sendMessage($args[0]);
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