<?php

namespace veroxcode\Guardian\Utils;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Raycast
{

    /**
     * @param AxisAlignedBB $boundingBox
     * @param Vector3 $start
     * @param Vector3 $direction
     * @param float $distance
     * @return bool
     */
    public static function isBBOnLine(AxisAlignedBB $boundingBox, Vector3 $start, Vector3 $direction, float $distance): bool
    {

        $rayVec = $start;
        $rayVec = $rayVec->add(0, 1.62, 0);

        for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01){
            $rayVec = $rayVec->addVector($direction->multiply($rayDist));
            $onRay = $boundingBox->expandedCopy(0.3, 0.3, 0.3)->isVectorInside($rayVec);

            if ($onRay){
                return true;
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @param Vector3 $start
     * @param Vector3 $direction
     * @param float $distance
     * @return Block|null
     */
    public static function getBlockOnLine(Player $player, Vector3 $start, Vector3 $direction, float $distance): ?Block
    {

        $rayVec = $start;
        $rayVec = $rayVec->add(0, 1.62, 0);

        for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01){
            $rayVec = $rayVec->addVector($direction->multiply($rayDist));
            $loc = new Vector3($rayVec->getX(), $rayVec->getY(), $rayVec->getZ());

            $eligible = true;
            $block = $player->getWorld()->getBlock($loc);

            if (str_contains(strtolower($block->getName()), "grass") && !$block->isFullCube() || str_contains(strtolower($block->getName()), "layer") && !$block->isFullCube()){
                $eligible = false;
            }

            if ($block->isTransparent()){
                if (!($block instanceof Bed || $block instanceof Glass || $block instanceof Chest)) {
                    $eligible = false;
                }
            }

            if ($block->isSolid() && $eligible){
                return $block;
            }
        }
        return null;
    }

}