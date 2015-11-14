<?php

namespace SmartFish;

class Util
{
    /**
     * @param float $value
     * @param float $min
     * @param float $max
     *
     * @return float
     */
    static public function clamp($value, $min, $max)
    {
        return min($max, max($min, $value));
    }

    /**
     * @return float
     */
    static public function getRandomClamped()
    {
        return static::getRandomFloat() - static::getRandomFloat();
    }

    /**
     * @return float
     */
    static public function getRandomFloat()
    {
        return mt_rand(0, 1000) / 1000;
    }
}
