<?php

class Utils
{
    /**
     * @param float $arg
     * @param float $min
     * @param float $max
     *
     * @return float
     */
    static public function Clamp($arg, $min, $max)
    {
        if ($arg < $min) {
            $arg = $min;
        }

        if ($arg > $max) {
            $arg = $max;
        }

        return $arg;
    }

    /**
     * @return float
     */
    static public function RandomClamped()
    {
        return static::RandFloat() - static::RandFloat();
    }

    /**
     * @return float
     */
    static public function RandFloat()
    {
        return mt_rand(0, 1000) / 1000;
    }
}
