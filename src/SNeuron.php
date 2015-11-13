<?php

class SNeuron
{
    /**
     * @var int
     */
    private $m_NumInputs;

    /**
     * @var float[]
     */
    private $m_vecWeight;

    /**
     * @param int $NumInputs
     */
    public function __construct($NumInputs)
    {
        $this->m_NumInputs = $NumInputs;
        // we need an additional weight for the bias hence the +1
        for ($i = 0; $i < $NumInputs + 1; $i++) {
            // set up the weights with an initial random value
            $this->m_vecWeight[] = Utils::RandomClamped();
        }
    }

    /**
     * @return int
     */
    public function getMNumInputs()
    {
        return $this->m_NumInputs;
    }

    /**
     * @param int $index
     *
     * @return float
     *
     * @throws OutOfRangeException
     */
    public function getWeight($index)
    {
        if (!isset($this->m_vecWeight[$index])) {
            throw new OutOfRangeException(sprintf('no weight defined for index %s', $index));
        }
        return $this->m_vecWeight[$index];
    }

    /**
     * @param int $index
     * @param float $weight
     */
    public function setWeight($index, $weight)
    {
        $this->m_vecWeight[$index] = $weight;
    }
}
