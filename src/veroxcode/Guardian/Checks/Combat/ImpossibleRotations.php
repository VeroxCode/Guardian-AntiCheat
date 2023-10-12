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

namespace veroxcode\Guardian\Checks\Combat;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Rotations;
use function abs;

class ImpossibleRotations extends Check {
	public function __construct() {
		parent::__construct("ImpossibleRotations");
	}

	public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user) : void {
		$delta = abs($packet->getYaw() - $player->getLocation()->getYaw());
		$delta = Rotations::wrapAngleTo180_float($delta);

		if ($delta > 3.5) {
			if ($packet->getHeadYaw() == $packet->getYaw()) {
				if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
					$user->increaseViolation($this->getName());
				}
			} else {
				$user->decreaseViolation($this->getName(), 2);
			}
		}

		if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
			Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
			$user->setPunishNext(true);
		}
	}
}