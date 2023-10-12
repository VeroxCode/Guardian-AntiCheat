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

use pocketmine\math\Vector3;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use function strpos;
use function substr_replace;

class Punishments {

	public static function punishPlayer(Player $player, Check $check, User $user, ?Vector3 $position, ?string $punishment) : void {
		if ($punishment == null) {
			return;
		}

		switch ($punishment) {
			case "Cancel":
				if ($position != null) {
					$player->teleport($position);
					$user->resetViolation($check->getName());
				}
				break;
			case "Kick":
				self::KickUser($player);
				break;
			case "Ban":
				self::BanUser($player);
				break;
		}
	}

	public static function KickUser(Player $player) : void {
		$config = Guardian::getInstance()->getConfig();
		$message = $config->get("kick-message");
		$prefix = $config->get("prefix");

		$msgPrefixPos = strpos($message, "%PREFIX%");
		$message = substr_replace($message, $prefix, $msgPrefixPos, 8);

		$player->kick($message);
	}

	public static function BanUser(Player $player) : void {
		$config = Guardian::getInstance()->getConfig();
		$message = $config->get("ban-message");
		$prefix = $config->get("prefix");

		$msgPrefixPos = strpos($message, "%PREFIX%");
		$message = substr_replace($message, $prefix, $msgPrefixPos, 8);

		$Ban = new BanEntry($player->getName());
		$Ban->setReason($message);
		Guardian::getInstance()->getServer()->getNameBans()->add($Ban);
		$player->kick($message);
	}
}