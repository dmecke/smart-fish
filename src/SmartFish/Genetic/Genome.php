<?php

namespace SmartFish\Genetic;

use SmartFish\Util;

class Genome
{
    const CROSSOVER_RATE = 0.7;
    const MUTATION_RATE = 0.1;
    const MAX_PERTUBATION = 0.3;

    /**
     * @var float[]
     */
    private $weights = [];

    /**
     * @var float
     */
    private $fitness = 0.0;

    /**
     * @param float[] $weights
     */
    public function __construct(array $weights = [])
    {
        $this->weights = $weights;
    }

    /**
     * @return float
     */
    public function getFitness()
    {
        return $this->fitness;
    }

    /**
     * @param float $fitness
     */
    public function setFitness($fitness)
    {
        $this->fitness = $fitness;
    }

    /**
     * @param float $amount
     */
    public function addFitness($amount)
    {
        $this->fitness += $amount;
    }

    /**
     * @return float[]
     */
    public function getWeights()
    {
        return $this->weights;
    }

    /**
     * @param int $index
     *
     * @return float
     */
    public function getWeight($index)
    {
        return $this->weights[$index];
    }

    /**
     * @return Genome
     *
     * mutates a genome by perturbing its weights by an amount not greater than self::MAX_PERTUBATION
     */
    public function mutate()
    {
        $weights = $this->weights;
        // traverse the genome and mutate each weight dependent on the mutation rate
        foreach ($weights as $i => $weight) {
            // do we perturb this weight?
            if (Util::getRandomFloat() < self::MUTATION_RATE) {
                // add or subtract a small value to the weight
                $weights[$i] += (Util::getRandomClamped() * self::MAX_PERTUBATION);
            }
        }
        return new Genome($weights);
    }

    /**
     * @param Genome $genome
     *
     * @return Genome
     *
     * given parents and storage for the offspring this method performs crossover according to the GAs crossover rate
     */
    public function crossoverWith(Genome $genome)
    {
        // just return parents as offspring dependent on the rate or if parents are the same
        if ((Util::getRandomFloat() > self::CROSSOVER_RATE) || ($this == $genome)) {
            return new Genome($this->weights);
        }

        $crossoverPoint = mt_rand(0, count($this->weights) - 1);

        $weights = array_merge(
            $this->getWeightsSegment(0, $crossoverPoint),
            $this->getWeightsSegment($crossoverPoint, count($this->weights))
        );

        return new Genome($weights);
    }

    /**
     * @param int $start
     * @param int $end
     *
     * @return float[]
     */
    private function getWeightsSegment($start, $end)
    {
        $weights = [];
        for ($i = $start; $i < $end; $i++) {
            $weights[] = $this->getWeight($i);
        }

        return $weights;
    }

    /**
     * @param int $numberOfWeights
     *
     * @return Genome
     */
    static public function createWithRandomWeight($numberOfWeights)
    {
        $weights = [];
        for ($i = 0; $i < $numberOfWeights; $i++) {
            $weights[] = Util::getRandomClamped();
        }

        return new Genome($weights);
    }
}
