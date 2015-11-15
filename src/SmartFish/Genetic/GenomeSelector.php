<?php

namespace SmartFish\Genetic;

use Exception;
use SmartFish\System\Util;

class GenomeSelector
{
    /**
     * @param Genome[] $genomes
     * @param int $amount
     * @param int $numberOfCopies
     *
     * @return Genome[]
     */
    public function selectElite(array $genomes, $amount, $numberOfCopies)
    {
        $genomes = $this->sortByFitness($genomes);

        $eliteGenomes = [];
        for ($i = $amount; $i > 0; $i--) {
            $index = count($genomes) - $i;

            for ($j = 0; $j < $numberOfCopies; $j++) {
                $eliteGenomes[] = $genomes[$index];
            }
        }

        return $eliteGenomes;
    }

    /**
     * @param Genome[] $genomes
     *
     * @return Genome
     *
     * @throws Exception
     */
    public function selectRandomGenomeWeighted(array $genomes)
    {
        $genomes = $this->sortByFitness($genomes);

        $fitnessTotal = 0;
        foreach ($genomes as $genome) {
            $fitnessTotal += $genome->getFitness();
        }

        // generate a random number between 0 & total fitness count
        $slice = Util::getRandomFloat() * $fitnessTotal;

        // go through the chromosones adding up the fitness so far
        $fitnessSoFar = 0;

        foreach ($genomes as $genome) {
            $fitnessSoFar += $genome->getFitness();

            // if the fitness so far > random number return the chromo at this point
            if ($fitnessSoFar >= $slice) {
                $theChosenOne = $genome;

                return $theChosenOne;
            }
        }

        throw new Exception('could not find a genome');
    }

    /**
     * @param Genome[] $genomes
     *
     * @return Genome[]
     */
    private function sortByFitness(array $genomes)
    {
        usort($genomes, function (Genome $a, Genome $b) {
            return $a->getFitness() - $b->getFitness();
        });

        return $genomes;
    }
}
