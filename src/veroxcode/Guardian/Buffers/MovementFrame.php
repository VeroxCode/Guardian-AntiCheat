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

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class MovementFrame {
	/*** @var int */
	private int $ServerTick;
	/*** @var int */
	private int $PlayerTick;
	/*** @var Vector3 */
	private Vector3 $Position;
	/*** @var Vector2 */
	private Vector2 $Rotation;
	/*** @var float */
	private float $HeadYaw;
	/*** @var bool */
	private bool $onGround;
	/*** @var AxisAlignedBB */
	private AxisAlignedBB $BoundingBox;


	public function __construct(int $ServerTick, int $PlayerTick, Vector3 $Position, Vector2 $Rotation, float $HeadYaw, bool $onGround, AxisAlignedBB $BoundingBox) {
		$this->ServerTick = $ServerTick;
		$this->PlayerTick = $PlayerTick;
		$this->Position = $Position;
		$this->Rotation = $Rotation;
		$this->HeadYaw = $HeadYaw;
		$this->onGround = $onGround;
		$this->BoundingBox = $BoundingBox;
	}


	public function getPlayerTick() : int {
		return $this->PlayerTick;
	}


	public function getServerTick() : int {
		return $this->ServerTick;
	}


	public function isOnGround() : bool {
		return $this->onGround;
	}


	public function getRotation() : Vector2 {
		return $this->Rotation;
	}


	public function getPosition() : Vector3 {
		return $this->Position;
	}


	public function getBoundingBox() : AxisAlignedBB {
		return $this->BoundingBox;
	}


	public function getHeadYaw() : float {
		return $this->HeadYaw;
	}
}