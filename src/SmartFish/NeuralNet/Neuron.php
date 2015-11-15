<?php

namespace SmartFish\NeuralNet;

use OutOfRangeException;
use SmartFish\System\Util;

class Neuron
{
    /**
     * @var float[]
     */
    private $weights;

    /**
     * @param int $inputs
     */
    public function __construct($inputs)
    {
        // we need an additional weight for the bias hence the +1
        for ($i = 0; $i < $inputs + 1; $i++) {
            // set up the weights with an initial random value
            $this->weights[] = Util::getRandomClamped();
        }
    }

    /**
     * @return int
     */
    public function getInputs()
    {
        return count($this->weights) - 1;
    }

    /**
     * @param int $index
     *
     * @return float
     *
     * @throws OutOfRangeException
     */
    public function getWeight($index)
    {
        if (!isset($this->weights[$index])) {
            throw new OutOfRangeException(sprintf('no weight defined for index %s', $index));
        }

        return $this->weights[$index];
    }

    /**
     * @param int $index
     * @param float $weight
     */
    public function setWeight($index, $weight)
    {
        $this->weights[$index] = $weight;
    }
}
