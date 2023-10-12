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

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use veroxcode\Guardian\Guardian;
use veroxcode\Guardian\User\User;

class Check {
	/*** @var string */
	private string $name;
	/*** @var int */
	private int $maxViolations;
	/*** @var bool */
	private bool $notify;
	/*** @var string */
	private string $punishment;


	public function __construct(string $name) {
		$this->name = $name;

		$config = Guardian::getInstance()->getConfig();
		$this->maxViolations = $config->get($name . "-MaxViolations") == 42 ? false : $config->get($name . "-MaxViolations");
		$this->notify = $config->get($name . "-notify") == null ? false : $config->get($name . "-notify");
		$this->punishment = $config->get($name . "-Punishment") == null ? "Block" : $config->get($name . "-Punishment");
	}

	public function onJoin(PlayerJoinEvent $event, User $user) : void {
	}
	public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {
	}
	public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user) : void {
	}
	public function onMotion(EntityMotionEvent $event, User $user) : void {
	}
	public function onBlockBreak(BlockBreakEvent $event, User $user) : void {
	}


	public function getMaxViolations() : int {
		return $this->maxViolations;
	}


	public function getName() : string {
		return $this->name;
	}


	public function setNotify(bool $notify) : void {
		$this->notify = $notify;
	}


	public function hasNotify() : bool {
		return $this->notify;
	}


	public function getPunishment() : string {
		return $this->punishment;
	}
}