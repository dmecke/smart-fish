<?php

class CNeuralNet
{
    /**
     * @var int
     */
    private $m_NumInputs = CParams::iNumInputs;

    /**
     * @var int
     */
    private $m_NumOutputs = CParams::iNumOutputs;

    /**
     * @var int
     */
    private $m_NumHiddenLayers = CParams::iNumHidden;

    /**
     * @var int
     */
    private $m_NeuronsPerHiddenLyr = CParams::iNeuronsPerHiddenLayer;

    /**
     * @var SNeuronLayer[]
     */
    private $m_vecLayers = [];

    public function __construct()
    {
        $this->CreateNet();
    }

    /**
     * this method builds the ANN. The weights are all initially set to random values -1 < w < 1
     */
    public function CreateNet()
    {
        // create the layers of the network
        if ($this->m_NumHiddenLayers > 0) {
            // create first hidden layer
            $this->m_vecLayers[] = new SNeuronLayer($this->m_NeuronsPerHiddenLyr, $this->m_NumInputs);

            for ($i = 0; $i < $this->m_NumHiddenLayers - 1; $i++) {
                $this->m_vecLayers[] = new SNeuronLayer($this->m_NeuronsPerHiddenLyr, $this->m_NeuronsPerHiddenLyr);
            }

            // create output layer
            $this->m_vecLayers[] = new SNeuronLayer($this->m_NumOutputs, $this->m_NeuronsPerHiddenLyr);
        } else {
            // create output layer
            $this->m_vecLayers[] = new SNeuronLayer($this->m_NumOutputs, $this->m_NumInputs);
        }
    }

    /**
     * @param float[] $weights
     *
     * given a vector of doubles this function replaces the weights in the NN with the new values
     */
    public function PutWeights(array $weights)
    {
        $cWeight = 0;

        // for each layer
        for ($i = 0; $i < $this->m_NumHiddenLayers + 1; $i++) {
            // for each neuron
            for ($j = 0; $j < $this->m_vecLayers[$i]->getMNumNeurons(); $j++) {
                // for each weight
                for ($k = 0; $k < $this->m_vecLayers[$i]->getNeuron($j)->getMNumInputs(); $k++) {
                    $this->m_vecLayers[$i]->getNeuron($j)->setWeight($k, $weights[$cWeight++]);
                }
            }
        }
    }

    /**
     * @return int
     *
     * returns the total number of weights needed for the net
     */
    public function GetNumberOfWeights()
    {
        $weights = 0;

        // for each layer
        for ($i = 0; $i < $this->m_NumHiddenLayers + 1; $i++) {
            // for each neuron
            for ($j = 0; $j < $this->m_vecLayers[$i]->getMNumNeurons(); $j++) {
                // for each weight
                for ($k = 0; $k < $this->m_vecLayers[$i]->getNeuron($j)->getMNumInputs(); $k++) {
                    $weights++;
                }
            }
        }

        return $weights;
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
    public function Update(array $inputs)
    {
        // stores the resultant outputs from each layer
        $outputs = [];

        // first check that we have the correct amount of inputs
        if (count($inputs) != $this->m_NumInputs) {
            throw new \Exception('wrong number of inputs');
        }

        // For each layer....
        for ($i = 0; $i < $this->m_NumHiddenLayers + 1; $i++) {
            if ($i > 0) {
                $inputs = $outputs;
            }

            $outputs = [];

            $cWeight = 0;

            // for each neuron sum the (inputs * corresponding weights).Throw
            // the total at our sigmoid function to get the output.
            for ($j = 0; $j < $this->m_vecLayers[$i]->getMNumNeurons(); $j++) {
                $netinput = 0.0;

                $NumInputs = $this->m_vecLayers[$i]->getNeuron($j)->getMNumInputs();

                // for each weight
                for ($k = 0; $k < $NumInputs - 1; $k++) {
                    // sum the weights x inputs
                    $netinput += $this->m_vecLayers[$i]->getNeuron($j)->getWeight($k) * $inputs[$cWeight++];
                }

                // add in the bias
                $netinput += $this->m_vecLayers[$i]->getNeuron($j)->getWeight($NumInputs - 1) * CParams::dBias;

                // we can store the outputs from each layer as we generate them.
                // The combined activation is first filtered through the sigmoid function
                $outputs[] = $this->Sigmoid($netinput, CParams::dActivationResponse);

                $cWeight = 0;
            }
        }

        return $outputs;
    }

    /**
     * @param float $netinput
     * @param float $response
     *
     * @return float
     */
    public function Sigmoid($netinput, $response)
    {
        return (1 / (1 + exp(-$netinput / $response)));
    }
}
