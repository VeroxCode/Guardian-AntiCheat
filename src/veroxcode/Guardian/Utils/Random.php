<?php

namespace veroxcode\Guardian\Utils;

class Random
{

    public static function clamp(float $min, float $max, float $current): float
    {
        return max($min, min($max, $current));
    }

}