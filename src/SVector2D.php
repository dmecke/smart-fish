<?php

class SVector2D
{
    /**
     * @var float
     */
    public $x;

    /**
     * @var float
     */
    public $y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param SVector2D $vector2D
     *
     * @return SVector2D
     */
    public function add(SVector2D $vector2D)
    {
        return new SVector2D($this->x + $vector2D->x, $this->y + $vector2D->y);
    }

    /**
     * @param SVector2D $vector2D
     *
     * @return SVector2D
     */
    public function subtract(SVector2D $vector2D)
    {
        return new SVector2D($this->x - $vector2D->x, $this->y - $vector2D->y);
    }

    /**
     * @param float $factor
     *
     * @return SVector2D
     */
    public function multiply($factor)
    {
        return new SVector2D($this->x * $factor, $this->y * $factor);
    }

    /**
     * @param SVector2D $v
     *
     * @return float
     */
    static public function Vec2DLength(SVector2D $v)
    {
        return sqrt(pow($v->x, 2) + pow($v->y, 2));
    }

    /**
     * @param SVector2D $v
     */
    static public function Vec2DNormalize(SVector2D $v)
    {
        $vector_length = static::Vec2DLength($v);

        if ($vector_length != 0) {
            $v->x = $v->x / $vector_length;
            $v->y = $v->y / $vector_length;
        }
    }
}
