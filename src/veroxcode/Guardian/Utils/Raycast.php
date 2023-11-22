<?php

namespace veroxcode\Guardian\Utils;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use veroxcode\Guardian\Guardian;

class Raycast
{

    /**
     * @param Vector3 $position
     * @param Vector3 $start
     * @param Vector3 $direction
     * @param float $distance
     * @return bool
     */
    public static function isBBOnLine(Vector3 $position, Vector3 $start, Vector3 $direction, float $distance): bool
    {
        $boundingBox = self::constructPlayerHitbox($position);
        $start->add(0, 1.62, 0);

        for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01){
            $checkVec = clone $start;
            $checkVec = $checkVec->addVector($direction->multiply($rayDist));
            $onRay = $boundingBox->isVectorInside($checkVec);

            if ($onRay){
                return true;
            }
        }
        return false;
    }

    /**
     * @param Vector3 $position
     * @return AxisAlignedBB
     */
    public static function constructPlayerHitbox(Vector3 $position) : AxisAlignedBB
    {
        return new AxisAlignedBB(
            $position->getX() - (Constants::HITBOX_WIDTH / 2),
            $position->getY() - 0.3,
            $position->getZ() - (Constants::HITBOX_WIDTH / 2),
            $position->getX() + (Constants::HITBOX_WIDTH / 2),
            $position->getY() + Constants::HITBOX_HEIGHT,
            $position->getZ() + (Constants::HITBOX_WIDTH / 2)
        );
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

        for ($rayDist = 0; $rayDist < $distance; $rayDist += 0.01){
            $checkVec = clone $start;
            $checkVec = $checkVec->addVector($direction->multiply($rayDist));

            $block = $player->getWorld()->getBlock($checkVec);
            $collisions = $block->getCollisionBoxes();

            foreach ($collisions as $boundingBox){
                $eligible = $boundingBox->isVectorInside($checkVec);

                if ($eligible){
                    return $block;
                }
            }
        }
        return null;
    }

}