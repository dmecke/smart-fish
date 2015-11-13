<?php

class SGenome
{
    /**
     * @var float[]
     */
    private $vecWeights = [];

    /**
     * @var float
     */
    private $dFitness = 0.0;

    /**
     * @param float[] $vecWeights
     */
    public function __construct(array $vecWeights = [])
    {
        $this->vecWeights = $vecWeights;
    }

    /**
     * @return float
     */
    public function getDFitness()
    {
        return $this->dFitness;
    }

    /**
     * @param float $vecWeight
     */
    public function addVecWeights($vecWeight)
    {
        $this->vecWeights[] = $vecWeight;
    }

    /**
     * @return float[]
     */
    public function getVecWeights()
    {
        return $this->vecWeights;
    }

    /**
     * @param float $dFitness
     */
    public function setDFitness($dFitness)
    {
        $this->dFitness = $dFitness;
    }
}
