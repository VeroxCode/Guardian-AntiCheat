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

namespace veroxcode\Guardian\Checks;

use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use function strpos;
use function substr_replace;

class Notifier {

	public static function NotifyFlag(string $name, User $user, Check $Check, int $Violation, bool $notify) : void {
		$config = Guardian::getInstance()->getConfig();
		$user->increaseAlertCount($Check->getName());

		if ($user->getAlertCount($Check->getName()) < Guardian::getInstance()->getConfig()->get($Check->getName() . "-AlertFrequency")) {
			return;
		}

		if (!Guardian::getInstance()->getConfig()->get("enable-debug")) {
			if ($notify) {
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

		foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$notifyUser = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
			$hasNotifications = $notifyUser->hasNotifications();

			if ($hasNotifications) {
				$player->sendMessage($message);
			}
		}
		$user->resetAlertCount($Check->getName());
	}


	public static function NotifyPlayers(string $name, User $user, Check $Check) : void {
		$config = Guardian::getInstance()->getConfig();
		$message = $config->get("alert-message");
		$prefix = $config->get("prefix");

		$msgPrefixPos = strpos($message, "%PREFIX%");
		$message = substr_replace($message, $prefix, $msgPrefixPos, 8);
		$msgPlayerPos = strpos($message, "%PLAYER%");
		$message = substr_replace($message, $name, $msgPlayerPos,8);
		$msgCheckPos = strpos($message, "%CHECK%");
		$message = substr_replace($message, $Check->getName(), $msgCheckPos, 7);

		foreach (Guardian::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$notifyUser = Guardian::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
			$hasNotifications = $notifyUser->hasNotifications();

			if ($player->hasPermission("guardian.notify")) {
				if ($hasNotifications) {
					$player->sendMessage($message);
				}
			}
		}
		$user->resetAlertCount($Check->getName());
	}
}