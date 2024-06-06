<?php

namespace veroxcode\Guardian;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Listener\EventListener;
use veroxcode\Guardian\Panel\AdminPanel;
use veroxcode\Guardian\User\UserManager;
use veroxcode\Guardian\Utils\Constants;
use veroxcode\Guardian\Utils\DiscordWebhook;

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
            $data = $this->config->get($key);
            if ($this->config->get($key) == null || $this->config->get($key) == ""){
                $this->getServer()->getLogger()->warning("missing $key");
                $this->config->set($key, $default->get($key));
            }
        }

        $this->getSavedConfig()->set("config-version", Constants::CONFIG_VERSION);
        $this->getSavedConfig()->save();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->userManager = new UserManager();
        $this->checkManager = new CheckManager();

    }

    public function onDisable(): void
    {
        $this->getSavedConfig()->save();
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

               switch ($args[0]){
                   case "help":
                       $sender->sendMessage("$prefix §fhelp §8- Lists all Commands");
                       $sender->sendMessage("$prefix §fpanel §8- Opens the Admin Panel\n");
                       $sender->sendMessage("$prefix §fwebtest §8- Test your Discord Webhook\n");

                       $this->getSavedConfig()->save();
                       return true;
                   case "panel":
                       if ($sender instanceof Player) {
                           if ($sender->hasPermission("guardian.admin")) {
                               AdminPanel::open($sender);
                               return true;
                           }
                       }
                       break;
                   case "web-test":
                       if ($sender instanceof Player) {
                           DiscordWebhook::TestNotification();
                           return true;
                       }
                       break;
                   default:
                       break;
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

    public function WebhookEnabled() : bool
    {
        return $this->getSavedConfig()->get("use-webhook");
    }

}