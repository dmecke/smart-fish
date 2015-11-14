<?php

use Nubs\Vectorix\Vector;

class Food implements JsonSerializable
{
    const SIZE = 2;

    /**
     * @var Vector
     */
    private $position;

    public function __construct()
    {
        $this->position = new Vector([mt_rand(0, Simulation::WIDTH), mt_rand(0, Simulation::HEIGHT)]);
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'position' => ['x' => $this->position->components()[0], 'y' => $this->position->components()[1]],
        ];
    }

    /**
     * @return Vector
     */
    public function getPosition()
    {
        return $this->position;
    }
}
