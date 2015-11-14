<?php

namespace SmartFish\NeuralNet;

class NeuronLayer
{
    /**
     * @var Neuron[]
     */
    private $neurons;

    /**
     * @param int $numberOfNeurons
     * @param int $numberOfInputsPerNeuron
     */
    public function __construct($numberOfNeurons, $numberOfInputsPerNeuron)
    {
        for ($i = 0; $i < $numberOfNeurons; $i++) {
            $this->neurons[] = new Neuron($numberOfInputsPerNeuron);
        }
    }

    /**
     * @return int
     */
    public function getNumberOfNeurons()
    {
        return count($this->neurons);
    }

    /**
     * @param int $index
     *
     * @return Neuron
     */
    public function getNeuron($index)
    {
        return $this->neurons[$index];
    }
}
