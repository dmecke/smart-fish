<?php

class Genome
{
    /**
     * @var float[]
     */
    private $weights = [];

    /**
     * @var float
     */
    private $fitness = 0.0;

    /**
     * @param float[] $vecWeights
     */
    public function __construct(array $vecWeights = [])
    {
        $this->weights = $vecWeights;
    }

    /**
     * @return float
     */
    public function getFitness()
    {
        return $this->fitness;
    }

    /**
     * @param float $weight
     */
    public function addWeight($weight)
    {
        $this->weights[] = $weight;
    }

    /**
     * @return float[]
     */
    public function getWeights()
    {
        return $this->weights;
    }

    /**
     * @param float $fitness
     */
    public function setFitness($fitness)
    {
        $this->fitness = $fitness;
    }
}
