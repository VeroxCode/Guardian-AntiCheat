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

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Raycast;
use function ceil;
use function count;

class Hitbox extends Check {
	public function __construct() {
		parent::__construct("Hitbox");
	}

	public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {
		$player = $event->getDamager();
		$victim = $event->getEntity();

		if ($player instanceof Player && $victim instanceof Player) {
			if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN || $event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
				return;
			}

			$ray = Raycast::isBBOnLine($victim->getBoundingBox(), $player->getPosition(), $player->getDirectionVector(), $player->getPosition()->distance($victim->getPosition()));
			if ($ray) {
				return;
			}

			$victimUUID = $victim->getUniqueId()->toString();
			$victimUser = Guardian::getInstance()->getUserManager()->getUser($victimUUID);

			$ping = $player->getNetworkSession()->getPing();
			$rewindTicks = ceil($ping / 50) + 1;

			if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks) {
				return;
			}

			$rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
			$rewindray = Raycast::isBBOnLine($rewindBuffer->getBoundingBox(), $player->getPosition(), $player->getDirectionVector(), $player->getPosition()->distance($victim->getPosition()));

			if (!$rewindray) {
				if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
					$user->increaseViolation($this->getName(), 2);
				}
			} else {
				$user->decreaseViolation($this->getName(), 1);
			}

			if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
				Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
				$event->cancel();
			}
		}
	}
}