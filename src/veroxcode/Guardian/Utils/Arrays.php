<?php

namespace veroxcode\Guardian\Utils;

class Arrays
{

    /**
     * @param array $array
     * @return array
     */
    public static function removeFirst(array $array) : array
    {
        array_shift($array);
        return $array;
    }

}