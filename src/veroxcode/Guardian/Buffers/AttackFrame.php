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

namespace veroxcode\Guardian\Buffers;

class AttackFrame {
	/*** @var int */
	private int $ServerTick;
	/*** @var int */
	private int $Ping;
	/*** @var float */
	private float $LastAttack;


	public function __construct(int $ServerTick, int $Ping, float $LastAttack) {
		$this->ServerTick = $ServerTick;
		$this->Ping = $Ping;
		$this->LastAttack = $LastAttack;
	}


	public function getServerTick() : int {
		return $this->ServerTick;
	}


	public function getPing() : int {
		return $this->Ping;
	}


	public function getLastAttack() : float {
		return $this->LastAttack;
	}
}