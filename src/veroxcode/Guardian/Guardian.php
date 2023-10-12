<?php

/*
 *
 *   _____                     _ _
 *  / ____|                   | (_)
 * | |  __ _   _  __ _ _ __ __| |_  __ _ _ __
 * | | |_ | | | |/ _` | '__/ _` | |/ _` | '_ \
 * | |__| | |_| | (_| | | | (_| | | (_| | | | |
 *  \_____|\__,_|\__,_|_|  \__,_|_|\__,_|_| |_|
 *                 _   _      _                _
 *     /\         | | (_)    | |              | |
 *    /  \   _ __ | |_ _  ___| |__   ___  __ _| |_
 *   / /\ \ | '_ \| __| |/ __| '_ \ / _ \/ _` | __|
 *  / ____ \| | | | |_| | (__| | | |  __/ (_| | |_
 * /_/    \_\_| |_|\__|_|\___|_| |_|\___|\__,_|\__|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Copyright (c) 2023 by VeroxCode <https://github.com/VeroxCode>
 */

declare(strict_types=1);

namespace veroxcode\Guardian;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use veroxcode\Guardian\Checks\CheckManager;
use veroxcode\Guardian\Listener\EventListener;
use veroxcode\Guardian\User\UserManager;
use function fclose;
use function file_exists;
use function rename;
use function stream_get_contents;
use function yaml_parse;

class Guardian extends PluginBase implements \pocketmine\event\Listener {
	private static Guardian $instance;

	public UserManager $userManager;
	public CheckManager $checkManager;

	public function onEnable() : void {
		self::$instance = $this;

		$this->checkConfig();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->userManager = new UserManager();
		$this->checkManager = new CheckManager();
	}

	/**
	 * @throws JsonException
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		$config = $this->getConfig();
		$prefix = $config->get("prefix");

		if ($command->getName() == "guardian") {
			if (isset($args[0])) {
				if ($args[0] == "help") {
					$sender->sendMessage(
						$prefix . "§f help §8- Lists all Commands\n"
						. $prefix . "§f debug §8- Enable/Disable Debug Mode\n"
						. $prefix . "§f notifications §8- Enable/Disable Notifications for yourself\n"
						. $prefix . "§f notify <Check> §8- Enable/Disable Notifications for certain Checks");
					$this->getConfig()->save();
					return true;
				}

				if ($args[0] == "debug") {
					$debug = $this->getConfig()->get("enable-debug");
					$this->getConfig()->set("enable-debug", !$debug);
					$sender->sendMessage($prefix . " §8Done.");
					$this->getConfig()->save();
					return true;
				}

				if ($args[0] == "notify") {
					if (!isset($args[1])) {
						return false;
					}

					$newnotify = $this->getConfig()->get($args[1] . "-notify");
					if ($this->getCheckManager()->getCheckByName($args[1]) != null) {
						$this->getCheckManager()->getCheckByName($args[1])->setNotify(!$newnotify);
						$this->getConfig()->set($args[1] . "-notify", !$newnotify);
						$sender->sendMessage($prefix . " §8Done.");
						$this->getConfig()->save();
						return true;
					}
				}

				if ($args[0] == "notifications") {
					if ($sender instanceof Player) {
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


	public static function getInstance() : Guardian {
		return self::$instance;
	}


	public function getCheckManager() : CheckManager {
		return $this->checkManager;
	}


	public function getUserManager() : UserManager {
		return $this->userManager;
	}


	/**
	 * Updates the configuration to the latest version.
	 */
	private function checkConfig() : void {
		$log = $this->getLogger();
		$pluginConfigResource = $this->getResource("config.yml");
		$pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
		fclose($pluginConfigResource);
		$config = $this->getConfig();

		if (!file_exists($this->getDataFolder() . "/config.yml")) {
			$this->saveDefaultConfig();
			return;
		}

		if ($pluginConfig === false) {
			$log->critical("Cannot check or detect configuration, is currupted plugin?");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		if ($config->get("config-version") === $pluginConfig["config-version"]) {
			return;
		}

		$log->notice(TF::RED . "An outdated configuration detected.");
		$log->notice(TF::GREEN . "The outdated plugin is renamed as \"old-config.yml\"!");
		@rename($this->getDataFolder() . "/config.yml", $this->getDataFolder() . "/old-config.yml");
		$this->saveDefaultConfig();
	}
}