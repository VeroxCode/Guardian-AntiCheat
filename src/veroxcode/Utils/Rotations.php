<?php

namespace veroxcode\Utils;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function fmod;

class Rotations
{

    public static function getRotationsNeeded(Entity $entity, Player $p): float
    {
        $diffX = $entity->getPosition()->x - $p->getPosition()->x;
        $diffZ = $entity->getPosition()->z - $p->getPosition()->getZ();

        $yaw = (atan2($diffZ, $diffX) * 180.0 / M_PI) - 90.0;

        return ($p->getLocation()->getYaw() + self::wrapAngleTo180_float($yaw - $p->getLocation()->getYaw()));
    }

    public static function getPitchNeeded(Entity $entity, Player $p): float
    {
        $diffX = $entity->getPosition()->x - $p->getPosition()->x;
        $diffY = ($entity->boundingBox->minY + $entity->boundingBox->maxY) / 2.0 - ($p->getPosition()->getY() + $p->getEyeHeight());
        $diffZ = $entity->getPosition()->z - $p->getPosition()->getZ();
        $dist = sqrt((float) ($diffX * $diffX + $diffZ * $diffZ));
        $pitch = (-(atan2($diffY, $dist) * 180.0 / 3.141592653589793));

        return ($p->getLocation()->getPitch() + self::wrapAngleTo180_float($pitch - $p->getLocation()->getPitch()));
    }

    public static function getRotations2Vec(Vector3 $from, Vector3 $to, float $yaw): float
    {
        $diffX = $to->getX() - $from->getX();
        $diffZ = $to->getZ() - $from->getZ();
        $calcyaw = (atan2($diffZ, $diffX) * 180.0 / M_PI) - 90;

        return self::wrapAngleTo180_float($yaw)+ self::wrapAngleTo180_float($calcyaw - $yaw);
    }

    public static function wrapAngleTo180_float(float $d): float
    {
        $d = fmod($d, 360.0);

        if ($d >= 180.0) {
            $d -= 360.0;
        }

        if ($d < -180.0) {
            $d += 360.0;
        }

        return $d;
    }

    public static function getBlockRotations(Player $p, float $x, float $y, float $z): array
    {
        $var4 = $x - $p->getPosition()->x + 0.5;
        $var5 = $z - $p->getPosition()->z + 0.5;
        $var6 = $y - ($p->getPosition()->y + $p->getEyeHeight() - 1.0);
        $var7 = sqrt($var4 * $var4 + $var5 * $var5);
        $var8 = atan2($var5, $var4) * 180.0 / M_PI - 90.0;
        return [$var8, -(atan2($var6, $var7) * 180.0 / M_PI)];
    }

    public static function getRotationPosition(float $distance, float $yaw, Vector3 $position): Vector3
    {
        $yawCalc = $yaw * (pi() / 180);
        $sin = -sin($yawCalc);
        $cos = cos($yawCalc);

        $x = $sin * $distance;
        $z = $cos * $distance;

        return new Vector3($position->x + $x, 0.0, $position->z + $z);
    }

    public static function sqrt_double(float $v): float
    {
        return (float)sqrt($v);
    }


}
