<?php

namespace veroxcode\Guardian\Utils;

use pocketmine\block\Block;
use pocketmine\block\BlueIce;
use pocketmine\block\FrostedIce;
use pocketmine\block\Ice;
use pocketmine\block\PackedIce;

class Blocks
{

    public static function hasIceBelow(Block $block) : bool
    {
        return ($block instanceof Ice || $block instanceof BlueIce || $block instanceof PackedIce);
    }

}