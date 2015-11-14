<?php

namespace SmartFish\Genetic;

class Algorithm
{
    const NUMBER_OF_ELITE = 3;
    const NUMBER_OF_COPIES_PER_ELITE = 1;

    /**
     * @param Genome[] $oldGenomes
     *
     * @return Genome[]
     *
     * takes a population of genomes and runs the algorithm through one cycle.
     * Returns a new population of genomes.
     */
    public function epoch(array $oldGenomes)
    {
        $selector = new GenomeSelector();

        $newGenomes = $selector->selectElite($oldGenomes, self::NUMBER_OF_ELITE, self::NUMBER_OF_COPIES_PER_ELITE);

        while (count($newGenomes) < count($oldGenomes)) {
            $mum = $selector->selectRandomGenomeWeighted($oldGenomes);
            $dad = $selector->selectRandomGenomeWeighted($oldGenomes);

            $newGenomes[] = $mum->crossoverWith($dad)->mutate();
        }

        return $newGenomes;
    }
}
