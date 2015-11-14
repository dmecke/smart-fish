<?php

namespace SmartFish\NeuralNet;

class Net
{
    /**
     * @var int
     */
    private $inputs;

    /**
     * @var int
     */
    private $outputs;

    /**
     * @var int
     */
    private $hiddenLayers;

    /**
     * @var int
     */
    private $neuronsPerHiddenLayer;

    /**
     * @var int
     */
    private $activation;

    /**
     * @var int
     */
    private $bias;

    /**
     * @var NeuronLayer[]
     */
    private $layers = [];

    /**
     * @param int $inputs
     * @param int $outputs
     * @param int $hiddenLayers
     * @param int $neuronsPerHiddenLayer
     * @param int $activation
     * @param int $bias
     */
    public function __construct($inputs, $outputs, $hiddenLayers, $neuronsPerHiddenLayer, $activation = 1, $bias = -1)
    {
        $this->inputs = $inputs;
        $this->outputs = $outputs;
        $this->hiddenLayers = $hiddenLayers;
        $this->neuronsPerHiddenLayer = $neuronsPerHiddenLayer;
        $this->activation = $activation;
        $this->bias = $bias;

        $this->createNet();
    }

    /**
     * this method builds the ANN. The weights are all initially set to random values -1 < w < 1
     */
    private function createNet()
    {
        // create the layers of the network
        if ($this->hiddenLayers > 0) {
            // create first hidden layer
            $this->layers[] = new NeuronLayer($this->neuronsPerHiddenLayer, $this->inputs);

            for ($i = 0; $i < $this->hiddenLayers - 1; $i++) {
                $this->layers[] = new NeuronLayer($this->neuronsPerHiddenLayer, $this->neuronsPerHiddenLayer);
            }

            // create output layer
            $this->layers[] = new NeuronLayer($this->outputs, $this->neuronsPerHiddenLayer);
        } else {
            // create output layer
            $this->layers[] = new NeuronLayer($this->outputs, $this->inputs);
        }
    }

    /**
     * @param float[] $weights
     *
     * given a vector of doubles this function replaces the weights in the NN with the new values
     */
    public function updateWeights(array $weights)
    {
        $index = 0;

        // for each layer
        for ($i = 0; $i < $this->hiddenLayers + 1; $i++) {
            // for each neuron
            for ($j = 0; $j < $this->layers[$i]->getNumberOfNeurons(); $j++) {
                // for each weight
                for ($k = 0; $k < $this->layers[$i]->getNeuron($j)->getInputs(); $k++) {
                    $this->layers[$i]->getNeuron($j)->setWeight($k, $weights[$index++]);
                }
            }
        }
    }

    /**
     * @return int
     *
     * returns the total number of weights needed for the net
     */
    public function getNumberOfWeights()
    {
        $numberOfWeights = 0;

        // for each layer
        for ($i = 0; $i < $this->hiddenLayers + 1; $i++) {
            // for each neuron
            for ($j = 0; $j < $this->layers[$i]->getNumberOfNeurons(); $j++) {
                // for each weight
                for ($k = 0; $k < $this->layers[$i]->getNeuron($j)->getInputs(); $k++) {
                    $numberOfWeights++;
                }
            }
        }

        return $numberOfWeights;
    }

    /**
     * @param float[] $inputs
     *
     * @return float[]
     *
     * @throws \Exception
     *
     * given an input vector this function calculates the output vector
     */
    public function update(array $inputs)
    {
        // stores the resultant outputs from each layer
        $outputs = [];

        // first check that we have the correct amount of inputs
        if (count($inputs) != $this->inputs) {
            throw new \Exception('wrong number of inputs');
        }

        // For each layer....
        for ($i = 0; $i < $this->hiddenLayers + 1; $i++) {
            if ($i > 0) {
                $inputs = $outputs;
            }

            $outputs = [];

            $index = 0;

            // for each neuron sum the (inputs * corresponding weights).Throw
            // the total at our sigmoid function to get the output.
            for ($j = 0; $j < $this->layers[$i]->getNumberOfNeurons(); $j++) {
                $inputValue = 0.0;

                $NumInputs = $this->layers[$i]->getNeuron($j)->getInputs();

                // for each weight
                for ($k = 0; $k < $NumInputs - 1; $k++) {
                    // sum the weights x inputs
                    $inputValue += $this->layers[$i]->getNeuron($j)->getWeight($k) * $inputs[$index++];
                }

                // add in the bias
                $inputValue += $this->layers[$i]->getNeuron($j)->getWeight($NumInputs - 1) * $this->bias;

                // we can store the outputs from each layer as we generate them.
                // The combined activation is first filtered through the sigmoid function
                $outputs[] = $this->sigmoid($inputValue);

                $index = 0;
            }
        }

        return $outputs;
    }

    /**
     * @param float $inputValue
     *
     * @return float
     */
    private function sigmoid($inputValue)
    {
        return (1 / (1 + exp(-$inputValue / $this->activation)));
    }
}
