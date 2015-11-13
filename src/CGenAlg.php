<?php

class CGenAlg
{
    /**
     * @var SGenome[]
     */
    private $m_vecPop = [];

    /**
     * @var int
     */
    private $m_iPopSize;

    /**
     * @var int
     */
    private $m_iChromoLength;

    /**
     * @var float
     */
    private $m_dTotalFitness = 0;

    /**
     * @var float
     */
    private $m_dBestFitness = 0;

    /**
     * @var float
     */
    private $m_dAverageFitness = 0;

    /**
     * @var float
     */
    private $m_dWorstFitness = 99999999;

    /**
     * @var int
     */
    private $m_iFittestGenome = 0;

    /**
     * @var float
     */
    private $m_dMutationRate;

    /**
     * @var float
     */
    private $m_dCrossoverRate;

    /**
     * @param int $popsize
     * @param float $MutRat
     * @param float $CrossRat
     * @param int $numweights
     */
    public function __construct($popsize, $MutRat, $CrossRat, $numweights)
    {
        $this->m_iPopSize = $popsize;
        $this->m_dMutationRate = $MutRat;
        $this->m_dCrossoverRate = $CrossRat;
        $this->m_iChromoLength = $numweights;

        // initialise population with chromosomes consisting of random
        // weights and all fitnesses set to zero
        for ($i = 0; $i < $this->m_iPopSize; $i++) {
            $this->m_vecPop[] = new SGenome();

            for ($j = 0; $j < $this->m_iChromoLength; $j++) {
                $this->m_vecPop[$i]->addVecWeights(Utils::RandomClamped());
            }
        }
    }

    /**
     * @return SGenome[]
     */
    public function GetChromos()
    {
        return $this->m_vecPop;
    }

    /**
     * @return float
     */
    public function AverageFitness()
    {
        return $this->m_dTotalFitness / $this->m_iPopSize;
    }

    /**
     * @return float
     */
    public function BestFitness()
    {
        return $this->m_dBestFitness;
    }

    /**
     * @param SGenome[] $old_pop
     *
     * @return SGenome[]
     *
     * takes a population of chromosones and runs the algorithm through one cycle.
     * Returns a new population of chromosones.
     */
    public function Epoch(array $old_pop)
    {
        // assign the given population to the classes population
        $this->m_vecPop = $old_pop;

        // reset the appropriate variables
        $this->Reset();

        // sort the population (for scaling and elitism)
        usort($this->m_vecPop, function(SGenome $a, SGenome $b) {
            if ($a->getDFitness() == $b->getDFitness()) {
                return 0;
            }

            return ($a->getDFitness() < $b->getDFitness()) ? -1 : 1;
        });

        // calculate best, worst, average and total fitness
        $this->CalculateBestWorstAvTot();

        // create a temporary vector to store new chromosones
        $vecNewPop = [];

        // Now to add a little elitism we shall add in some copies of the
        // fittest genomes. Make sure we add an EVEN number or the roulette
        // wheel sampling will crash
        if (!(CParams::iNumCopiesElite * CParams::iNumElite % 2)) {
            $vecNewPop = $this->GrabNBest(CParams::iNumElite, CParams::iNumCopiesElite);
        }

        // repeat until a new population is generated
        while (count($vecNewPop) < $this->m_iPopSize) {
            // grab two chromosones
            $mum = $this->GetChromoRoulette();
            $dad = $this->GetChromoRoulette();

            // create some offspring via crossover
            $baby1 = [];
            $baby2 = [];

            $this->Crossover($mum->getVecWeights(), $dad->getVecWeights(), $baby1, $baby2);

            // now we mutate
            $baby1 = $this->Mutate($baby1);
            $baby2 = $this->Mutate($baby2);

            // now copy into vecNewPop population
            $vecNewPop[] = new SGenome($baby1);
            $vecNewPop[] = new SGenome($baby2);
        }

        // finished so assign new pop back into m_vecPop
        $this->m_vecPop = $vecNewPop;

        return $this->m_vecPop;
    }

    public function Reset()
    {
        $this->m_dTotalFitness = 0;
        $this->m_dBestFitness = 0;
        $this->m_dWorstFitness = 9999999;
        $this->m_dAverageFitness = 0;
    }

    public function CalculateBestWorstAvTot()
    {
        $this->m_dTotalFitness = 0;

        $HighestSoFar = 0;
        $LowestSoFar = 9999999;

        for ($i = 0; $i < $this->m_iPopSize; $i++) {
            // update fittest if necessary
            if ($this->m_vecPop[$i]->getDFitness() > $HighestSoFar) {
                $HighestSoFar = $this->m_vecPop[$i]->getDFitness();
                $this->m_iFittestGenome = $i;
                $this->m_dBestFitness = $HighestSoFar;
            }

            // update worst if necessary
            if ($this->m_vecPop[$i]->getDFitness() < $LowestSoFar) {
                $LowestSoFar = $this->m_vecPop[$i]->getDFitness();
                $this->m_dWorstFitness = $LowestSoFar;
            }

            $this->m_dTotalFitness += $this->m_vecPop[$i]->getDFitness();
        }

        $this->m_dAverageFitness = $this->m_dTotalFitness / $this->m_iPopSize;
    }

    /**
     * @param int $NBest
     * @param int $NumCopies
     *
     * @return SGenome[]
     *
     * This works like an advanced form of elitism by inserting NumCopies copies of the NBest most fittest genomes into a population vector
     */
    public function GrabNBest($NBest, $NumCopies)
    {
        $Pop = [];
        // add the required amount of copies of the n most fittest to the supplied vector
        while($NBest--) {
            for ($i = 0; $i < $NumCopies; $i++) {
                $Pop[] = $this->m_vecPop[($this->m_iPopSize - 1) - $NBest];
            }
        }

        return $Pop;
    }

    /**
     * @return SGenome
     *
     * @throws Exception
     */
    public function GetChromoRoulette()
    {
        // generate a random number between 0 & total fitness count
        $Slice = Utils::RandFloat() * $this->m_dTotalFitness;

        // go through the chromosones adding up the fitness so far
        $FitnessSoFar = 0;

        for ($i = 0; $i < $this->m_iPopSize; $i++) {
            $FitnessSoFar += $this->m_vecPop[$i]->getDFitness();

            // if the fitness so far > random number return the chromo at this point
            if ($FitnessSoFar >= $Slice) {
                $TheChosenOne = $this->m_vecPop[$i];

                return $TheChosenOne;
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
    public function Crossover($mum, $dad, &$baby1, &$baby2)
    {
        // just return parents as offspring dependent on the rate or if parents are the same
        if ((Utils::RandFloat() > $this->m_dCrossoverRate) || ($mum == $dad)) {
            $baby1 = $mum;
            $baby2 = $dad;

            return;
        }

        // determine a crossover point
        $cp = mt_rand(0, $this->m_iChromoLength - 1);

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
    public function Mutate(array $chromo)
    {
        // traverse the chromosome and mutate each weight dependent on the mutation rate
        for ($i = 0; $i < count($chromo); $i++) {
            // do we perturb this weight?
            if (Utils::RandFloat() < $this->m_dMutationRate) {
                // add or subtract a small value to the weight
                $chromo[$i] += (Utils::RandomClamped() * CParams::dMaxPerturbation);
            }
        }

        return $chromo;
    }
}
