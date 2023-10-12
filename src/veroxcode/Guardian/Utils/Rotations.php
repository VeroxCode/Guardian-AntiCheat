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

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function atan2;
use function cos;
use function fmod;
use function pi;
use function sin;
use function sqrt;

class Rotations {
	public static function getRotationsNeeded(Entity $entity, Player $p) : float {
		$diffX = $entity->getPosition()->x - $p->getPosition()->x;
		$diffZ = $entity->getPosition()->z - $p->getPosition()->getZ();

		$yaw = (atan2($diffZ, $diffX) * 180.0 / M_PI) - 90.0;

		return ($p->getLocation()->getYaw() + self::wrapAngleTo180_float($yaw - $p->getLocation()->getYaw()));
	}

	public static function getPitchNeeded(Entity $entity, Player $p) : float {
		$diffX = $entity->getPosition()->x - $p->getPosition()->x;
		$diffY = ($entity->boundingBox->minY + $entity->boundingBox->maxY) / 2.0 - ($p->getPosition()->getY() + $p->getEyeHeight());
		$diffZ = $entity->getPosition()->z - $p->getPosition()->getZ();
		$dist = sqrt((float) ($diffX * $diffX + $diffZ * $diffZ));
		$pitch = (-(atan2($diffY, $dist) * 180.0 / 3.141592653589793));

		return ($p->getLocation()->getPitch() + self::wrapAngleTo180_float($pitch - $p->getLocation()->getPitch()));
	}

	public static function getRotations2Vec(Vector3 $from, Vector3 $to, float $yaw) : float {
		$diffX = $to->getX() - $from->getX();
		$diffZ = $to->getZ() - $from->getZ();
		$calcyaw = (atan2($diffZ, $diffX) * 180.0 / M_PI) - 90;

		return self::wrapAngleTo180_float($yaw) + self::wrapAngleTo180_float($calcyaw - $yaw);
	}

	public static function wrapAngleTo180_float(float $d) : float {
		$d = fmod($d, 360.0);

		if ($d >= 180.0) {
			$d -= 360.0;
		}

		if ($d < -180.0) {
			$d += 360.0;
		}

		return $d;
	}

	public static function getBlockRotations(Player $p, float $x, float $y, float $z) : array {
		$var4 = $x - $p->getPosition()->x + 0.5;
		$var5 = $z - $p->getPosition()->z + 0.5;
		$var6 = $y - ($p->getPosition()->y + $p->getEyeHeight() - 1.0);
		$var7 = sqrt($var4 * $var4 + $var5 * $var5);
		$var8 = atan2($var5, $var4) * 180.0 / M_PI - 90.0;
		return [$var8, -(atan2($var6, $var7) * 180.0 / M_PI)];
	}

	public static function getRotationPosition(float $distance, float $yaw, Vector3 $position) : Vector3 {
		$yawCalc = $yaw * (pi() / 180);
		$sin = -sin($yawCalc);
		$cos = cos($yawCalc);

		$x = $sin * $distance;
		$z = $cos * $distance;

		return new Vector3($position->x + $x, 0.0, $position->z + $z);
	}

	public static function sqrt_double(float $v) : float {
		return (float) sqrt($v);
	}
}
