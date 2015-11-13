<?php

class NeuronTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Neuron
     */
    private $neuron;

    protected function setUp()
    {
        $this->neuron = new Neuron(3);
    }

    /**
     * @test
     */
    public function it_has_the_given_number_of_inputs()
    {
        $this->assertSame(3, $this->neuron->getInputs());
    }

    /**
     * @test
     */
    public function it_has_weights_for_all_inputs()
    {
        $this->assertNotNull($this->neuron->getWeight(0));
        $this->assertNotNull($this->neuron->getWeight(1));
        $this->assertNotNull($this->neuron->getWeight(2));
        $this->assertNotNull($this->neuron->getWeight(3)); // this one is needed for the bias!
    }

    /**
     * @test
     */
    public function it_throws_an_exction_when_weight_of_non_existing_input_is_requested()
    {
        $this->setExpectedException(OutOfRangeException::class);

        $this->neuron->getWeight(4);
    }

    /**
     * @test
     */
    public function it_applies_an_adjusted_weight()
    {
        $this->neuron->setWeight(1, 0.66);

        $this->assertSame(0.66, $this->neuron->getWeight(1));
    }
}
