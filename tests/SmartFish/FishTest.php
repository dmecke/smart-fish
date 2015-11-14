<?php

namespace SmartFish;

use PHPUnit_Framework_TestCase;
use SmartFish\Genetic\Genome;
use SmartFish\NeuralNet\Net;

class FishTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Fish
     */
    private $fish;

    public function setUp()
    {
        $net = new Net(Fish::BRAIN_INPUTS, Fish::BRAIN_OUTPUTS, Fish::BRAIN_HIDDEN_LAYERS, Fish::BRAIN_NEURONS_PER_HIDDEN_LAYER);
        $genome = Genome::createWithRandomWeight($net->getNumberOfWeights());

        $this->fish = new Fish($net, $genome);
    }

    /**
     * @test
     */
    public function its_fitness_increases_when_eating_food()
    {
        $food = new Food();

        $this->fish->eat($food);

        $this->assertSame(1.0, $this->fish->getFitness());
    }

    /**
     * @test
     */
    public function its_fitness_decreases_when_eating_poisonous_food()
    {
        $food = new Food(-1.0);

        $this->fish->eat($food);

        $this->assertSame(-1.0, $this->fish->getFitness());
    }
}
