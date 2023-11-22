<?php

namespace veroxcode\Guardian\Utils;

use pocketmine\block\Block;
use pocketmine\block\BlueIce;
use pocketmine\block\Cobweb;
use pocketmine\block\Ice;
use pocketmine\block\Ladder;
use pocketmine\block\Lava;
use pocketmine\block\PackedIce;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Vine;
use pocketmine\block\Water;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Blocks
{

    /**
     * @param Block $block
     * @return bool
     */
    public static function hasIceBelow(Block $block): bool
    {
        return ($block instanceof Ice || $block instanceof BlueIce || $block instanceof PackedIce);
    }

    /**
     * @param Player $player
     * @param int $radius
     * @return Block|null
     */
    public static function getBlockInsideBB(Player $player, int $radius = 3): ?Block
    {
        $World = $player->getWorld();

        $minX = $player->getBoundingBox()->minX;
        $maxX = $player->getBoundingBox()->maxX;
        $minY = $player->getBoundingBox()->minY;
        $maxY = $player->getBoundingBox()->maxY;
        $minZ = $player->getBoundingBox()->minZ;
        $maxZ = $player->getBoundingBox()->maxZ;

        for ($x = $minX; $x < $maxX; $x++){
            for ($y = $minY; $y < $maxY; $y++){
                for ($z = $minZ; $z < $maxZ; $z++){
                    $blockPos = new Vector3($x, $y, $z);
                    return $World->getBlock($blockPos);
                }
            }
        }
        return null;
    }

    /**
     * @param Player $player
     * @return Block|null
     */
    public static function getBlockBelow(Player $player): ?Block
    {
       return $player->getWorld()->getBlock($player->getPosition()->getSide(Facing::DOWN));
    }

    /**
     * @param Player $player
     * @return Block|null
     */
    public static function getBlockAbove(Player $player): ?Block
    {
        $position = $player->getPosition()->add(0, 1.0, 0);
        return $player->getWorld()->getBlock($position->getSide(Facing::UP));
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasBlocksAbove(Player $player): bool
    {
        return self::getBlockAbove($player)->isSolid();
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInsideLiquid(Player $player): bool
    {
        $World = $player->getWorld();

        $minX = $player->getBoundingBox()->minX;
        $maxX = $player->getBoundingBox()->maxX;
        $minY = $player->getBoundingBox()->minY;
        $maxY = $player->getBoundingBox()->maxY;
        $minZ = $player->getBoundingBox()->minZ;
        $maxZ = $player->getBoundingBox()->maxZ;

        for ($x = $minX; $x < $maxX; $x++){
            for ($y = $minY; $y < $maxY; $y++){
                for ($z = $minZ; $z < $maxZ; $z++){
                    $blockPos = new Vector3($x, $y, $z);
                    $block = $World->getBlock($blockPos);

                    if (($block instanceof Water || $block instanceof Lava)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isOnClimbable(Player $player): bool
    {

        $World = $player->getWorld();

        $minX = $player->getBoundingBox()->minX;
        $maxX = $player->getBoundingBox()->maxX;
        $minY = $player->getBoundingBox()->minY;
        $maxY = $player->getBoundingBox()->maxY;
        $minZ = $player->getBoundingBox()->minZ;
        $maxZ = $player->getBoundingBox()->maxZ;

        for ($x = $minX; $x < $maxX; $x++){
            for ($y = $minY; $y < $maxY; $y++){
                for ($z = $minZ; $z < $maxZ; $z++){
                    $blockPos = new Vector3($x, $y, $z);
                    $block = $World->getBlock($blockPos);

                    $blockBelow = self::getBlockBelow($player);
                    $blockAbove = self::getBlockAbove($player);

                    if (($block instanceof Ladder || $block instanceof Vine)){
                        return true;
                    }

                    if (($blockBelow instanceof Ladder || $blockBelow instanceof Vine)){
                        return true;
                    }

                    if (($blockAbove instanceof Ladder || $blockAbove instanceof Vine)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isOnSteppable(Player $player): bool
    {

        $World = $player->getWorld();

        $minX = $player->getBoundingBox()->minX;
        $maxX = $player->getBoundingBox()->maxX;
        $minY = $player->getBoundingBox()->minY;
        $maxY = $player->getBoundingBox()->maxY;
        $minZ = $player->getBoundingBox()->minZ;
        $maxZ = $player->getBoundingBox()->maxZ;

        for ($x = $minX; $x < $maxX; $x++){
            for ($y = $minY; $y < $maxY; $y++){
                for ($z = $minZ; $z < $maxZ; $z++){
                    $blockPos = new Vector3($x, $y, $z);
                    $block = $World->getBlock($blockPos);

                    if (($block instanceof Slab || $block instanceof Stair)){
                        return true;
                    }

                    if ((self::getBlockBelow($player) instanceof Stair || self::getBlockBelow($player) instanceof Slab)){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInCobweb(Player $player): bool
    {

        $World = $player->getWorld();
        $blockPos = new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ());

        for ($side = 1; $side < 6; $side++){
            if ($World->getBlock($blockPos->getSide($side)) instanceof Cobweb){
                return true;
            }
        }

        if (self::getBlockBelow($player) instanceof Cobweb){
            return true;
        }

        if (self::getBlockAbove($player) instanceof Cobweb){
            return true;
        }

        return false;
    }


}