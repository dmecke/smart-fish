<?php

class Simulation
{
    const WIDTH = 400;
    const HEIGHT = 400;

    const TICKS_PER_GENERATION = 2000;

    const NUMBER_OF_SWEEPERS = 30;
    const NUMBER_OF_MINES = 40;

    /**
     * @var Genome[]
     */
    private $genomes = [];

    /**
     * @var MineSweeper[]
     */
    private $mineSweepers = [];

    /**
     * @var Vector[]
     */
    private $mines = [];

    /**
     * @var GeneticAlgorithm
     */
    private $geneticAlgorithm;

    /**
     * @var int
     */
    private $m_NumWeightsInNN;

    /**
     * @var int
     */
    private $tick = 0;

    /**
     * @var int
     */
    private $generation = 0;

    public function __construct()
    {
        // let's create the mine sweepers
        for ($i = 0; $i < self::NUMBER_OF_SWEEPERS; $i++) {
            $this->mineSweepers[] = new MineSweeper();
        }

        // get the total number of weights used in the sweepers
        // NN so we can initialise the GA
        $this->m_NumWeightsInNN = $this->mineSweepers[0]->getNumberOfWeights();

        // initialize the genetic algorithm class
        $this->geneticAlgorithm = new GeneticAlgorithm(self::NUMBER_OF_SWEEPERS, $this->m_NumWeightsInNN);

        // get the weights from the ga and insert into the sweepers brains
        $this->genomes = $this->geneticAlgorithm->getChromos();

        for ($i = 0; $i < self::NUMBER_OF_SWEEPERS; $i++) {
            $this->mineSweepers[$i]->putWeights($this->genomes[$i]->getWeights());
        }

        // initialize mines in random positions within the application window
        for ($i = 0; $i < self::NUMBER_OF_MINES; $i++) {
            $this->mines[] = new Vector(mt_rand(0, Simulation::WIDTH), mt_rand(0, Simulation::HEIGHT));
        }
    }

    public function Update()
    {
        // run the sweepers through CParams::iNumTicks amount of cycles. During
        // this loop each sweepers NN is constantly updated with the appropriate
        // information from its surroundings. The output from the NN is obtained
        // and the sweeper is moved. If it encounters a mine its fitness is
        // updated appropriately,
        if ($this->tick++ < self::TICKS_PER_GENERATION)
        {
            for ($i = 0; $i < self::NUMBER_OF_SWEEPERS; $i++) {
                // update the NN and position
                if (!$this->mineSweepers[$i]->update($this->mines)) {
                    // error in processing the neural net
                    throw new \Exception('Wrong amount of NN inputs!');
                }

                // see if it's found a mine
                $GrabHit = $this->mineSweepers[$i]->checkForMine($this->mines);

                if ($GrabHit >= 0) {
                    // we have discovered a mine so increase fitness
                    $this->mineSweepers[$i]->incrementFitness();

                    // mine found so replace the mine with another at a random position
                    $this->mines[$GrabHit] = new Vector(mt_rand(0, Simulation::WIDTH), mt_rand(0, Simulation::HEIGHT));
                }

                // update the chromos fitness score
                $this->genomes[$i]->setFitness($this->mineSweepers[$i]->getFitness());
            }
        } else {
            // Another generation has been completed.
            // Time to run the GA and update the sweepers with their new NNs

            // increment the generation counter
            $this->generation++;

            // reset cycles
            $this->tick = 0;

            // run the GA to create a new population
            $this->genomes = $this->geneticAlgorithm->epoch($this->genomes);

            // insert the new (hopefully)improved brains back into the sweepers
            // and reset their positions etc
            for ($i = 0; $i < self::NUMBER_OF_SWEEPERS; $i++) {
                $this->mineSweepers[$i]->putWeights($this->genomes[$i]->getWeights());

                $this->mineSweepers[$i]->reset();
            }
            $this->printGeneration();
        }
    }

    private function printGeneration()
    {
        $generation = $this->generation;
        $average = floor($this->geneticAlgorithm->averageFitness());
        $best = floor($this->geneticAlgorithm->bestFitness());
        $worst = floor($this->geneticAlgorithm->worstFitness());

        $generationString = str_pad(sprintf('Generation %s', $generation), 15);
        $averageString = str_pad(sprintf('Average: %s', $average), 13);
        $bestString = str_pad(sprintf('Best: %s', $best), 10);
        $worstString = str_pad(sprintf('Worst: %s', $worst), 10);
        $averageChart = str_repeat('=', $average);
        $bestChart = str_repeat('-', $best - $average);

        printf("%s %s %s %s %s%s\n", $generationString, $averageString, $worstString, $bestString, $averageChart, $bestChart);
    }
}
