<?php

class FishTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function its_fitness_increases_when_eating_food()
    {
        $food = new Food();
        $fish = new Fish();

        $fish->eat($food);

        $this->assertSame(1.0, $fish->getFitness());
    }

    /**
     * @test
     */
    public function its_fitness_decreases_when_eating_poisonous_food()
    {
        $food = new Food(-1.0);
        $fish = new Fish();

        $fish->eat($food);

        $this->assertSame(-1.0, $fish->getFitness());
    }
}
