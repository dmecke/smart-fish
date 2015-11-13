<?php

class GeneticAlgorithm
{
    const CROSSOVER_RATE = 0.7;
    const MUTATION_RATE = 0.1;
    const MAX_PERTUBATION = 0.3;
    const NUMBER_OF_ELITE = 4;
    const NUMBER_OF_COPIES_PER_ELITE = 1;

    /**
     * @var Genome[]
     */
    private $genomes = [];

    /**
     * @var int
     */
    private $population;

    /**
     * @var int
     */
    private $chromoLength;

    /**
     * @var float
     */
    private $fitnessTotal = 0;

    /**
     * @var float
     */
    private $fitnessBest = 0;

    /**
     * @var float
     */
    private $fitnessWorst = 99999999;

    /**
     * @param int $population
     * @param int $chromoLength
     */
    public function __construct($population, $chromoLength)
    {
        $this->population = $population;
        $this->chromoLength = $chromoLength;

        if (!$this->guardElite()) {
            throw new InvalidArgumentException('NUMBER_OF_ELITE * NUMBER_OF_COPIES_PER_ELITE must be even as the ChromeRoulette can only produce an even number');
        }

        // initialise population with chromosomes consisting of random
        // weights and all fitnesses set to zero
        for ($i = 0; $i < $this->population; $i++) {
            $this->genomes[] = new Genome();

            for ($j = 0; $j < $this->chromoLength; $j++) {
                $this->genomes[$i]->addWeight(Util::getRandomClamped());
            }
        }
    }

    /**
     * @return Genome[]
     */
    public function getChromos()
    {
        return $this->genomes;
    }

    /**
     * @return float
     */
    public function averageFitness()
    {
        return $this->fitnessTotal / $this->population;
    }

    /**
     * @return float
     */
    public function bestFitness()
    {
        return $this->fitnessBest;
    }

    /**
     * @return float
     */
    public function worstFitness()
    {
        return $this->fitnessWorst;
    }

    /**
     * @param Genome[] $oldGenomes
     *
     * @return Genome[]
     *
     * takes a population of chromosones and runs the algorithm through one cycle.
     * Returns a new population of chromosones.
     */
    public function epoch(array $oldGenomes)
    {
        // assign the given population to the classes population
        $this->genomes = $oldGenomes;

        // reset the appropriate variables
        $this->reset();

        // sort the population (for scaling and elitism)
        usort($this->genomes, function(Genome $a, Genome $b) {
            if ($a->getFitness() == $b->getFitness()) {
                return 0;
            }

            return ($a->getFitness() < $b->getFitness()) ? -1 : 1;
        });

        // calculate best, worst, average and total fitness
        $this->calculateStatistics();

        // Now to add a little elitism we shall add in some copies of the
        // fittest genomes.
        $newGenomes = $this->grabNBest();

        // repeat until a new population is generated
        while (count($newGenomes) < $this->population) {
            // grab two chromosones
            $mum = $this->getChromoRoulette();
            $dad = $this->getChromoRoulette();

            // create some offspring via crossover
            $babyOneWeights = [];
            $babyTwoWeights = [];

            $this->crossover($mum->getWeights(), $dad->getWeights(), $babyOneWeights, $babyTwoWeights);

            // now we mutate
            $babyOneWeights = $this->mutate($babyOneWeights);
            $babyTwoWeights = $this->mutate($babyTwoWeights);

            // now copy into newGenomes population
            $newGenomes[] = new Genome($babyOneWeights);
            $newGenomes[] = new Genome($babyTwoWeights);
        }

        // finished so assign new pop back into m_vecPop
        $this->genomes = $newGenomes;

        return $this->genomes;
    }

    private function reset()
    {
        $this->fitnessTotal = 0;
        $this->fitnessBest = 0;
        $this->fitnessWorst = 9999999;
    }

    private function calculateStatistics()
    {
        $this->fitnessTotal = 0;

        $highestSoFar = 0;
        $lowestSoFar = 9999999;

        for ($i = 0; $i < $this->population; $i++) {
            // update fittest if necessary
            if ($this->genomes[$i]->getFitness() > $highestSoFar) {
                $highestSoFar = $this->genomes[$i]->getFitness();
                $this->fitnessBest = $highestSoFar;
            }

            // update worst if necessary
            if ($this->genomes[$i]->getFitness() < $lowestSoFar) {
                $lowestSoFar = $this->genomes[$i]->getFitness();
                $this->fitnessWorst = $lowestSoFar;
            }

            $this->fitnessTotal += $this->genomes[$i]->getFitness();
        }
    }

    /**
     * @return Genome[]
     *
     * This works like an advanced form of elitism by inserting NumCopies copies of the NBest most fittest genomes into a population vector
     */
    private function grabNBest()
    {
        $NBest = self::NUMBER_OF_ELITE;
        $genomes = [];
        // add the required amount of copies of the n most fittest to the supplied vector
        while($NBest--) {
            for ($i = 0; $i < self::NUMBER_OF_COPIES_PER_ELITE; $i++) {
                $genomes[] = $this->genomes[($this->population - 1) - $NBest];
            }
        }

        return $genomes;
    }

    /**
     * @return Genome
     *
     * @throws Exception
     */
    private function getChromoRoulette()
    {
        // generate a random number between 0 & total fitness count
        $slice = Util::getRandomFloat() * $this->fitnessTotal;

        // go through the chromosones adding up the fitness so far
        $fitnessSoFar = 0;

        for ($i = 0; $i < $this->population; $i++) {
            $fitnessSoFar += $this->genomes[$i]->getFitness();

            // if the fitness so far > random number return the chromo at this point
            if ($fitnessSoFar >= $slice) {
                $theChosenOne = $this->genomes[$i];

                return $theChosenOne;
            }
        }

        throw new \Exception('could not find a chromo');
    }

    /**
     * @param float[] $mum
     * @param float[] $dad
     * @param float[] $baby1
     * @param float[] $baby2
     *
     * given parents and storage for the offspring this method performs crossover according to the GAs crossover rate
     */
    private function crossover($mum, $dad, &$baby1, &$baby2)
    {
        // just return parents as offspring dependent on the rate or if parents are the same
        if ((Util::getRandomFloat() > self::CROSSOVER_RATE) || ($mum == $dad)) {
            $baby1 = $mum;
            $baby2 = $dad;

            return;
        }

        // determine a crossover point
        $cp = mt_rand(0, $this->chromoLength - 1);

        // create the offspring
        for ($i = 0; $i < $cp; $i++) {
            $baby1[] = $mum[$i];
            $baby2[] = $dad[$i];
        }

        for ($i = $cp; $i < count($mum); $i++) {
            $baby1[] = $dad[$i];
            $baby2[] = $mum[$i];
        }

        return;
    }

    /**
     * @param float[] $chromo
     *
     * @return float[]
     *
     * mutates a chromosome by perturbing its weights by an amount not greater than CParams::dMaxPerturbation
     */
    private function mutate(array $chromo)
    {
        // traverse the chromosome and mutate each weight dependent on the mutation rate
        for ($i = 0; $i < count($chromo); $i++) {
            // do we perturb this weight?
            if (Util::getRandomFloat() < self::MUTATION_RATE) {
                // add or subtract a small value to the weight
                $chromo[$i] += (Util::getRandomClamped() * self::MAX_PERTUBATION);
            }
        }

        return $chromo;
    }

    /**
     * @return bool
     */
    private function guardElite()
    {
        return (self::NUMBER_OF_COPIES_PER_ELITE * self::NUMBER_OF_ELITE) % 2 == 0;
    }
}
