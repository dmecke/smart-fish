<?php

class Controller
{
    /**
     * @var SGenome[]
     */
    private $m_vecThePopulation = [];

    /**
     * @var CMineSweeper[]
     */
    private $m_vecSweepers = [];

    /**
     * @var SVector2D[]
     */
    private $m_vecMines = [];

    /**
     * @var CGenAlg
     */
    private $m_pGA;

    /**
     * @var int
     */
    private $m_NumSweepers = CParams::iNumSweepers;

    /**
     * @var int
     */
    private $m_NumMines = CParams::iNumMines;

    /**
     * @var int
     */
    private $m_NumWeightsInNN;

    /**
     * @var float[]
     */
    private $m_vecAvFitness;

    /**
     * @var float[]
     */
    private $m_vecBestFitness;

    /**
     * @var int
     */
    private $m_iTicks = 0;

    /**
     * @var int
     */
    private $m_iGenerations = 0;

    /**
     * @var int
     */
    private $cxClient = CParams::WindowWidth;

    /**
     * @var int
     */
    private $cyClient = CParams::WindowWidth;

    public function __construct()
    {
        // let's create the mine sweepers
        for ($i = 0; $i < $this->m_NumSweepers; $i++) {
            $this->m_vecSweepers[] = new CMineSweeper();
        }

        // get the total number of weights used in the sweepers
        // NN so we can initialise the GA
        $this->m_NumWeightsInNN = $this->m_vecSweepers[0]->GetNumberOfWeights();

        // initialize the genetic algorithm class
        $this->m_pGA = new CGenAlg($this->m_NumSweepers, CParams::dMutationRate, CParams::dCrossoverRate, $this->m_NumWeightsInNN);

        // get the weights from the ga and insert into the sweepers brains
        $this->m_vecThePopulation = $this->m_pGA->GetChromos();

        for ($i = 0; $i < $this->m_NumSweepers; $i++) {
            $this->m_vecSweepers[$i]->PutWeights($this->m_vecThePopulation[$i]->getVecWeights());
        }

        // initialize mines in random positions within the application window
        for ($i = 0; $i < $this->m_NumMines; $i++) {
            $this->m_vecMines[] = new SVector2D(mt_rand(0, $this->cxClient), mt_rand(0, $this->cyClient));
        }
    }

    public function Update()
    {
        // run the sweepers through CParams::iNumTicks amount of cycles. During
        // this loop each sweepers NN is constantly updated with the appropriate
        // information from its surroundings. The output from the NN is obtained
        // and the sweeper is moved. If it encounters a mine its fitness is
        // updated appropriately,
        if ($this->m_iTicks++ < CParams::iNumTicks)
        {
            for ($i = 0; $i < $this->m_NumSweepers; $i++) {
                // update the NN and position
                if (!$this->m_vecSweepers[$i]->Update($this->m_vecMines)) {
                    // error in processing the neural net
                    throw new \Exception('Wrong amount of NN inputs!');
                }

                // see if it's found a mine
                $GrabHit = $this->m_vecSweepers[$i]->CheckForMine($this->m_vecMines, CParams::dMineScale);

                if ($GrabHit >= 0) {
                    // we have discovered a mine so increase fitness
                    $this->m_vecSweepers[$i]->IncrementFitness();

                    // mine found so replace the mine with another at a random position
                    $this->m_vecMines[$GrabHit] = new SVector2D(mt_rand(0, $this->cxClient), mt_rand(0, $this->cyClient));
                }

                // update the chromos fitness score
                $this->m_vecThePopulation[$i]->setDFitness($this->m_vecSweepers[$i]->Fitness());
            }
        } else {
            // Another generation has been completed.
            // Time to run the GA and update the sweepers with their new NNs
            // update the stats to be used in our stat window
            $this->m_vecAvFitness[] = $this->m_pGA->AverageFitness();
            $this->m_vecBestFitness[] = $this->m_pGA->BestFitness();

            // increment the generation counter
            $this->m_iGenerations++;

            // reset cycles
            $this->m_iTicks = 0;

            // run the GA to create a new population
            $this->m_vecThePopulation = $this->m_pGA->Epoch($this->m_vecThePopulation);

            // insert the new (hopefully)improved brains back into the sweepers
            // and reset their positions etc
            for ($i = 0; $i < $this->m_NumSweepers; $i++) {
                $this->m_vecSweepers[$i]->PutWeights($this->m_vecThePopulation[$i]->getVecWeights());

                $this->m_vecSweepers[$i]->Reset();
            }
            $this->printGeneration();
        }
    }

    private function printGeneration()
    {
        $generation = $this->m_iGenerations;
        $average = floor($this->m_pGA->AverageFitness());
        $best = floor($this->m_pGA->BestFitness());

        $generationString = str_pad(sprintf('Generation %s', $generation), 15);
        $averageString = str_pad(sprintf('Average: %s', $average), 13);
        $bestString = str_pad(sprintf('Best: %s', $best), 10);
        $averageChart = str_repeat('=', $average);
        $bestChart = str_repeat('-', $best - $average);

        printf("%s %s %s %s%s\n", $generationString, $averageString, $bestString, $averageChart, $bestChart);
    }
}
