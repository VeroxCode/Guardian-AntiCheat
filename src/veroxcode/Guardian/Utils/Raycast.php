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

namespace veroxcode\Guardian\Utils;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function str_contains;
use function strtolower;

class Raycast {

	public static function isBBOnLine(AxisAlignedBB $boundingBox, Vector3 $start, Vector3 $direction, float $distance) : bool {
		$rayVec = $start;
		$rayVec = $rayVec->add(0, 1.62, 0);

		for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01) {
			$rayVec = $rayVec->addVector($direction->multiply($rayDist));
			$onRay = $boundingBox->expandedCopy(0.3, 0.3, 0.3)->isVectorInside($rayVec);

			if ($onRay) {
				return true;
			}
		}
		return false;
	}


	public static function getBlockOnLine(Player $player, Vector3 $start, Vector3 $direction, float $distance) : ?Block {
		$rayVec = $start;
		$rayVec = $rayVec->add(0, 1.62, 0);

		for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01) {
			$rayVec = $rayVec->addVector($direction->multiply($rayDist));
			$loc = new Vector3($rayVec->getX(), $rayVec->getY(), $rayVec->getZ());

			$eligible = true;
			$block = $player->getWorld()->getBlock($loc);

			if (str_contains(strtolower($block->getName()), "grass") && !$block->isFullCube() || str_contains(strtolower($block->getName()), "layer") && !$block->isFullCube()) {
				$eligible = false;
			}

			if ($block->isTransparent()) {
				if (!($block instanceof Bed || $block instanceof Glass || $block instanceof Chest)) {
					$eligible = false;
				}
			}

			if ($block->isSolid() && $eligible) {
				return $block;
			}
		}
		return null;
	}
}