<?php

namespace SmartFish\Simulation;

use JsonSerializable;
use SmartFish\Genetic\Algorithm;
use SmartFish\Genetic\Genome;
use SmartFish\NeuralNet\Net;
use SmartFish\System\Output;

class Simulation implements JsonSerializable
{
    const WIDTH = 400;
    const HEIGHT = 400;

    const NUMBER_OF_FISHES = 30;
    const NUMBER_OF_FOOD = 40;

    /**
     * @var Fish[]
     */
    private $fishes = [];

    /**
     * @var Food[]
     */
    private $foods = [];

    /**
     * @var int
     */
    private $tick = 0;

    /**
     * @var int
     */
    private $generation = 0;

    /**
     * @var int
     */
    private $ticksPerGeneration;

    /**
     * @var Output
     */
    private $output;

    /**
     * @param Output $output
     * @param int $ticksPerGeneration
     */
    public function __construct(Output $output, $ticksPerGeneration = 2000)
    {
        $this->output = $output;
        $this->ticksPerGeneration = $ticksPerGeneration;

        // let's create the fishes
        for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
            $neuralNet = $this->createNeuralNet();
            $genome = Genome::createWithRandomWeight($neuralNet->getNumberOfWeights());
            $this->fishes[] = new Fish($neuralNet, $genome);
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
        // and the fish is moved. If it finds food its fitness is updated appropriately
        if ($this->tick < $this->ticksPerGeneration) {
            $this->calculateTick();
        } else {
            $this->calculateEndOfGeneration();
        }

        $this->tick++;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'fishes' => $this->fishes,
            'foods' => $this->foods,
        ];
    }

    /**
     * @return float|null
     */
    public function bestFitness()
    {
        $best = null;
        foreach ($this->fishes as $fish) {
            if (null === $best || $fish->getFitness() > $best) {
                $best = $fish->getFitness();
            }
        }

        return $best;
    }

    /**
     * @return float|null
     */
    public function worstFitness()
    {
        $worst = null;
        foreach ($this->fishes as $fish) {
            if (null === $worst || $fish->getFitness() < $worst) {
                $worst = $fish->getFitness();
            }
        }

        return $worst;
    }

    /**
     * @return float
     */
    private function totalFitness()
    {
        $total = 0.0;
        foreach ($this->fishes as $fish) {
            $total += $fish->getFitness();
        }

        return $total;
    }

    /**
     * @return float
     */
    public function averageFitness()
    {
        return $this->totalFitness() / count($this->fishes);
    }

    private function calculateEndOfGeneration()
    {
        // increment the generation counter
        $this->generation++;

        // reset cycles
        $this->tick = 0;

        $genomes = [];
        foreach ($this->fishes as $fish) {
            $genomes[] = $fish->getGenome();
        }

        // run the GA to create a new population
        $geneticAlgorithm = new Algorithm();
        $genomes = $geneticAlgorithm->epoch($genomes);

        $this->output->outputGeneration($this);

        // insert the new (hopefully)improved brains back into the fishes and reset their positions etc
        for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
            $this->fishes[$i] = new Fish($this->createNeuralNet(), $genomes[$i]);
        }
    }

    private function calculateTick()
    {
        for ($i = 0; $i < self::NUMBER_OF_FISHES; $i++) {
            $fish = $this->fishes[$i];

            // update the NN and position
            $fish->update($this->foods);

            // see if it's found food
            $grabHit = $fish->checkForFood($this->foods);

            if ($grabHit >= 0) {
                // we have discovered food so increase fitness
                $fish->eat($this->foods[$grabHit]);

                // food found so replace it with another at a random position
                $this->foods[$grabHit] = new Food();
            }
        }
    }

    /**
     * @return Net
     */
    private function createNeuralNet()
    {
        return new Net(Fish::BRAIN_INPUTS, Fish::BRAIN_OUTPUTS, Fish::BRAIN_HIDDEN_LAYERS, Fish::BRAIN_NEURONS_PER_HIDDEN_LAYER);
    }

    /**
     * @return int
     */
    public function getGeneration()
    {
        return $this->generation;
    }
}
