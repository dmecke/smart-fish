<?php

class Simulation implements JsonSerializable
{
    const WIDTH = 400;
    const HEIGHT = 400;

    const TICKS_PER_GENERATION = 2000;

    const NUMBER_OF_FISHES = 30;
    const NUMBER_OF_FOOD = 40;

    /**
     * @var Genome[]
     */
    private $genomes = [];

    /**
     * @var Fish[]
     */
    private $fishes = [];

    /**
     * @var Food[]
     */
    private $foods = [];

    /**
     * @var GeneticAlgorithm
     */
    private $geneticAlgorithm;

    /**
     * @var int
     */
    private $numberOfWeights;

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
        // let's create the fishes
        for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
            $this->fishes[] = new Fish();
        }

        // get the total number of weights used in the fish NN so we can initialise the GA
        $this->numberOfWeights = $this->fishes[0]->getNumberOfWeights();

        // initialize the genetic algorithm class
        $this->geneticAlgorithm = new GeneticAlgorithm(self::NUMBER_OF_FISHES, $this->numberOfWeights);

        // get the weights from the ga and insert into the fish brains
        $this->genomes = $this->geneticAlgorithm->getChromos();

        for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
            $this->fishes[$i]->putWeights($this->genomes[$i]->getWeights());
        }

        // initialize food in random positions within the application window
        for ($i = 0; $i < self::NUMBER_OF_FOOD; $i++) {
            $this->foods[] = new Food();
        }
    }

    public function update()
    {
        // run the fish through all ticks of a generation. During
        // this loop each fish NN is constantly updated with the appropriate
        // information from its surroundings. The output from the NN is obtained
        // and the fish is moved. If it finds food its fitness is
        // updated appropriately,
        if ($this->tick++ < self::TICKS_PER_GENERATION)
        {
            for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
                // update the NN and position
                if (!$this->fishes[$i]->update($this->foods)) {
                    // error in processing the neural net
                    throw new \Exception('Wrong amount of NN inputs!');
                }

                // see if it's found food
                $grabHit = $this->fishes[$i]->checkForFood($this->foods);

                if ($grabHit >= 0) {
                    // we have discovered food so increase fitness
                    $this->fishes[$i]->eat($this->foods[$grabHit]);

                    // food found so replace it with another at a random position
                    $this->foods[$grabHit] = new Food();
                }

                // update the chromos fitness score
                $this->genomes[$i]->setFitness($this->fishes[$i]->getFitness());
            }
        } else {
            // Another generation has been completed.
            // Time to run the GA and update the fishes with their new NNs

            // increment the generation counter
            $this->generation++;

            // reset cycles
            $this->tick = 0;

            // run the GA to create a new population
            $this->genomes = $this->geneticAlgorithm->epoch($this->genomes);

            // insert the new (hopefully)improved brains back into the fishes and reset their positions etc
            for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
                $this->fishes[$i]->putWeights($this->genomes[$i]->getWeights());

                $this->fishes[$i]->reset();
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

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'fishes' => $this->fishes,
            'foods' => $this->foods,
        ];
    }
}
