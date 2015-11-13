<?php

class SNeuronLayer
{
    /**
     * @var int
     */
    private $m_NumNeurons;

    /**
     * @var SNeuron[]
     */
    private $m_vecNeurons;

    /**
     * @param int $NumNeurons
     * @param int $NumInputsPerNeuron
     */
    public function __construct($NumNeurons, $NumInputsPerNeuron)
    {
        $this->m_NumNeurons = $NumNeurons;
        for ($i = 0; $i < $NumNeurons; $i++) {
            $this->m_vecNeurons[] = new SNeuron($NumInputsPerNeuron);
        }
    }

    /**
     * @return int
     */
    public function getMNumNeurons()
    {
        return $this->m_NumNeurons;
    }

    /**
     * @param int $index
     *
     * @return SNeuron
     */
    public function getNeuron($index)
    {
        return $this->m_vecNeurons[$index];
    }
}
