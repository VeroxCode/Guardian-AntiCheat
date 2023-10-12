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
use pocketmine\player\Player;
use veroxcode\Guardian\Checks\Check;
use veroxcode\Guardian\Checks\Notifier;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;
use veroxcode\Guardian\Utils\Constants;
use function floor;

class AutoClicker extends Check {
	private float $CPS_LIMIT;

	public function __construct() {
		parent::__construct("AutoClicker");

		$config = Guardian::getInstance()->getConfig();
		$this->CPS_LIMIT = $config->get("CPS-Limit") == null ? Constants::CPS_LIMIT : $config->get("CPS-Limit");
	}

	public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {
		$player = $event->getDamager();

		if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
			return;
		}

		if ($player instanceof Player) {
			$hits = 0;

			foreach ($user->getAttackBuffer() as $attackFrame) {
				if ((Guardian::getInstance()->getServer()->getTick() - ($attackFrame->getServerTick() - floor($attackFrame->getPing() / 50))) < Guardian::getInstance()->getServer()->getTicksPerSecond()) {
					$hits++;
				}
			}

			if ($hits >= $this->CPS_LIMIT) {
				if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
					$user->increaseViolation($this->getName(), 1);
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