<?php

class Vector
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
     * @param Vector $vector2D
     *
     * @return Vector
     */
    public function add(Vector $vector2D)
    {
        return new Vector($this->x + $vector2D->x, $this->y + $vector2D->y);
    }

    /**
     * @param Vector $vector2D
     *
     * @return Vector
     */
    public function subtract(Vector $vector2D)
    {
        return new Vector($this->x - $vector2D->x, $this->y - $vector2D->y);
    }

    /**
     * @param float $factor
     *
     * @return Vector
     */
    public function multiply($factor)
    {
        return new Vector($this->x * $factor, $this->y * $factor);
    }

    /**
     * @param Vector $v
     *
     * @return float
     */
    static public function length(Vector $v)
    {
        return sqrt(pow($v->x, 2) + pow($v->y, 2));
    }

    /**
     * @param Vector $v
     */
    static public function normalize(Vector $v)
    {
        $vector_length = static::length($v);

        if ($vector_length != 0) {
            $v->x = $v->x / $vector_length;
            $v->y = $v->y / $vector_length;
        }
    }
}
