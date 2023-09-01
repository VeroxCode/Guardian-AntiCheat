<?php

namespace veroxcode\Utils;

use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use veroxcode\Guardian;

class Raycast
{

    public static function EntityOnLine(AxisAlignedBB $boundingBox, Vector3 $start, Vector3 $direction, float $distance): bool
    {

        $rayVec = $start;
        $rayVec = $rayVec->add(0, 1.62, 0);
        $rayVec = $rayVec->addVector($direction->multiply($distance));

        if($rayVec->x <= ($boundingBox->minX - 0.4) or $rayVec->x >= ($boundingBox->maxX + 0.4)){
            return false;
        }

        if($rayVec->y <= ($boundingBox->minY - 0.4) or $rayVec->y >= ($boundingBox->maxY + 0.4)){
            return false;
        }

        return $rayVec->z > ($boundingBox->minZ - 0.4) and $rayVec->z < ($boundingBox->maxZ + 0.4);
    }

}