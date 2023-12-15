<?php

namespace veroxcode\Guardian;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;
use pocketmine\Server;
use pocketmine\utils\Config;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Listener\EventListener;
use veroxcode\Guardian\User\UserManager;
use veroxcode\Guardian\Utils\Constants;

class Guardian extends PluginBase implements \pocketmine\event\Listener
{

    private static Guardian $instance;

    public ?Config $config;
    public UserManager $userManager;
    public CheckManager $checkManager;

    public function onEnable() : void
    {
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveResource("SavedConfig.yml");
        $default = new Config($this->getResourceFolder() . "config.yml", Config::YAML);
        $this->config = new Config($this->getDataFolder() . "SavedConfig.yml", Config::YAML);

        foreach ($default->getAll(true) as $key){
            if ($this->getSavedConfig()->get($key) == null){
                $this->getSavedConfig()->set($key, $default->get($key));
            }
        }

        $this->getSavedConfig()->set("config-version", Constants::CONFIG_VERSION);
        $this->getSavedConfig()->save();

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

       $config = $this->getSavedConfig();
       $prefix = $config->get("prefix");

       if ($command->getName() == "guardian") {
           if (isset($args[0])) {
               if ($args[0] == "help"){
                   $sender->sendMessage(
                       $prefix . "§f help §8- Lists all Commands\n"
                       .   $prefix . "§f debug §8- Enable/Disable Debug Mode\n"
                       .   $prefix . "§f notifications §8- Enable/Disable Notifications for yourself\n"
                       .   $prefix . "§f notify <Check> §8- Enable/Disable Notifications for certain Checks");
                       $this->getSavedConfig()->save();
                       return true;
                   }

               if ($args[0] == "debug"){
                   $debug = $this->getSavedConfig()->get("enable-debug");
                   $this->getSavedConfig()->set("enable-debug", !$debug);
                   $sender->sendMessage($prefix . " §8Done.");
                   $this->getSavedConfig()->save();
                   return true;
               }

               if ($args[0] == "notify"){
                   if (!isset($args[1])) {
                       return false;
                   }

                   $newnotify = $this->getSavedConfig()->get($args[1] . "-notify");
                   if ($this->getCheckManager()->getCheckByName($args[1]) != null){
                       $this->getCheckManager()->getCheckByName($args[1])->setNotify(!$newnotify);
                       $this->getSavedConfig()->set($args[1] . "-notify", !$newnotify);
                       $sender->sendMessage($prefix . " §8Done.");
                       $this->getSavedConfig()->save();
                       return true;
                   }
               }

               if ($args[0] == "notifications"){
                    if ($sender instanceof Player){
                        $uuid = $sender->getUniqueId()->toString();
                        $user = $this->getUserManager()->getUser($uuid);
                        $notifications = $user->hasNotifications();
                        $user->setNotifications(!$notifications);
                        $sender->sendMessage($prefix . " §8Done.");
                        return true;
                    }
               }
           }
       }
       return false;
   }

    public static function getInstance(): Guardian
   {
       return self::$instance;
   }

    public function getCheckManager(): CheckManager
   {
       return $this->checkManager;
   }

    public function getUserManager(): UserManager
    {
        return $this->userManager;
    }

    public function getSavedConfig(): Config
    {
        return $this->config;
    }

    public function debugEnabled(): bool
    {
        return $this->getSavedConfig()->get("enable-debug");
    }

}