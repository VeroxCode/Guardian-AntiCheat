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

use veroxcode\Guardian\Checks\Combat\AutoClicker;
use veroxcode\Guardian\Checks\Combat\Hitbox;
use veroxcode\Guardian\Checks\Combat\ImpossibleRotations;
use veroxcode\Guardian\Checks\Combat\Reach;
use veroxcode\Guardian\Checks\Movement\Timer;
use veroxcode\Guardian\Checks\Packets\BadPacketsA;
use veroxcode\Guardian\Checks\World\GhostHand;

class CheckManager {
	/** @var Check[] */
	public array $Checks = [];

	public function __construct() {
		$this->Checks[] = new Reach();
		$this->Checks[] = new Hitbox();
		$this->Checks[] = new Timer();
		$this->Checks[] = new AutoClicker();
		$this->Checks[] = new BadPacketsA();
		$this->Checks[] = new GhostHand();
		$this->Checks[] = new ImpossibleRotations();
	}

	/**
	 * @return Check[]
	 */
	public function getChecks() : array {
		return $this->Checks;
	}

	public function getCheckByName(string $name) : ?Check {
		foreach ($this->getChecks() as $check) {
			if ($check->getName() == $name) {
				return $check;
			}
		}
		return null;
	}
}