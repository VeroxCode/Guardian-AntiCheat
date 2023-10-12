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

namespace veroxcode\Guardian\Checks\World;

use pocketmine\block\Bed;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Checks\Punishments;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Raycast;
use function str_contains;
use function strtolower;

class GhostHand extends Check {
	public function __construct() {
		parent::__construct("GhostHand");
	}

	public function onBlockBreak(BlockBreakEvent $event, User $user) : void {
		if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN) {
			return;
		}

		$block = $event->getBlock();
		$player = $event->getPlayer();
		$distance = $player->getPosition()->distance($block->getPosition());
		$rayBlock = Raycast::getBlockOnLine($player, $player->getPosition(), $player->getDirectionVector(), $distance);

		if ($rayBlock != null) {
			if (str_contains(strtolower($block->getName()), "grass") && !$block->isFullCube() || str_contains(strtolower($block->getName()), "layer") && !$block->isFullCube()) {
				return;
			}

			if ($block->isTransparent()) {
				if (!($block instanceof Bed || $block instanceof Glass || $block instanceof Chest)) {
					return;
				}
			}

			if ($rayBlock !== $block) {
				$event->cancel();
				if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
					$user->increaseViolation($this->getName(), 2);
				}
			} else {
				$user->decreaseViolation($this->getName(), 1);
			}

			if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
				Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
				if ($this->getPunishment() != "Cancel") {
					Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
				}
			}
		}
	}
}