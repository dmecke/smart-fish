<?php

use Nubs\Vectorix\Vector;

class MineSweeper implements JsonSerializable
{
    const BRAIN_INPUTS = 4;
    const BRAIN_OUTPUTS = 2;
    const BRAIN_HIDDEN_LAYERS = 1;
    const BRAIN_NEURONS_PER_HIDDEN_LAYER = 6;

    const MAX_TURN_RATE = 0.3;
    const SIZE = 5;

    /**
     * @var NeuralNet
     */
    private $neuralNet;

    /**
     * @var Vector
     */
    private $position;

    /**
     * @var Vector
     */
    private $lookAt;

    /**
     * @var float
     */
    private $rotation;

    /**
     * @var float
     */
    private $speed;

    /**
     * @var float
     */
    private $leftTrack = 0.16;

    /**
     * @var float
     */
    private $rightTrack = 0.16;

    /**
     * @var float
     */
    private $fitness = 0;

    /**
     * @var int
     */
    private $closestMine;

    public function __construct()
    {
        $this->reset();
        $this->lookAt = Vector::nullVector(2);
        $this->neuralNet = new NeuralNet(self::BRAIN_INPUTS, self::BRAIN_OUTPUTS, self::BRAIN_HIDDEN_LAYERS, self::BRAIN_NEURONS_PER_HIDDEN_LAYER);
    }

    public function reset()
    {
        $this->position = new Vector([mt_rand(0, Simulation::WIDTH), mt_rand(0, Simulation::HEIGHT)]);
        $this->rotation = rand(0, 100) * pi() * 2;
        $this->fitness = 0;
    }

    public function incrementFitness()
    {
        $this->fitness++;
    }

    /**
     * @return float
     */
    public function getFitness()
    {
        return $this->fitness;
    }

    /**
     * @return int
     */
    public function getNumberOfWeights()
    {
        return $this->neuralNet->getNumberOfWeights();
    }

    /**
     * @param float[] $weights
     */
    public function putWeights(array $weights)
    {
        $this->neuralNet->putWeights($weights);
    }

    /**
     * @param Vector[] $mines
     *
     * @return int
     */
    public function checkForMine(array $mines)
    {
        $distance = $this->position->subtract($mines[$this->closestMine]);

        if ($distance->length() < (Mine::SIZE + self::SIZE)) {
            return $this->closestMine;
        }

        return -1;
    }
    
    /**
     * @param Vector[] $mines
     * 
     * @return bool
     *
     * @throws \Exception
     *
     * First we take sensor readings and feed these into the sweepers brain.
     * The inputs are:
     *
     * A vector to the closest mine (x, y)
     * The sweepers 'look at' vector (x, y)
     * We receive two outputs from the brain.. lTrack & rTrack.
     * So given a force for each track we calculate the resultant rotation
     * and acceleration and apply to current velocity vector.
     */
    public function update(array $mines)
    {
        // this will store all the inputs for the NN
        $inputs = [];

        // get vector to closest mine
        $closestMine = $this->getClosestMine($mines);

        // normalise it
        if ($closestMine->length() != 0) {
            $closestMine = $closestMine->normalize();
        }

        // add in vector to closest mine
        $inputs[] = $closestMine->components()[0];
        $inputs[] = $closestMine->components()[1];

        // add in sweepers look at vector
        $inputs[] = $this->lookAt->components()[0];
        $inputs[] = $this->lookAt->components()[1];


        // update the brain and get feedback
        $output = $this->neuralNet->update($inputs);

        // make sure there were no errors in calculating the output
        if (count($output) < self::BRAIN_OUTPUTS) {
            throw new \Exception(sprintf('number of output values (%s) does not match expected number (%s)', count($output), self::BRAIN_OUTPUTS));
        }

        // assign the outputs to the sweepers left & right tracks
        $this->leftTrack = $output[0];
        $this->rightTrack = $output[1];

        // calculate steering forces
        $rotationForce = $this->leftTrack - $this->rightTrack;

        // clamp rotation
        $rotationForce = Util::clamp($rotationForce, -self::MAX_TURN_RATE, self::MAX_TURN_RATE);

        $this->rotation += $rotationForce;

        $this->speed = ($this->leftTrack + $this->rightTrack);

        // update Look At
        $this->lookAt = new Vector([-sin($this->rotation), cos($this->rotation)]);

        // update position
        $this->position = $this->position->add($this->lookAt->multiplyByScalar($this->speed));

        // wrap around window limits
        if ($this->position->components()[0] > Simulation::WIDTH) {
            $this->position = new Vector([0, $this->position->components()[1]]);
        }
        if ($this->position->components()[0] < 0) {
            $this->position = new Vector([Simulation::WIDTH, $this->position->components()[1]]);
        }
        if ($this->position->components()[1] > Simulation::HEIGHT) {
            $this->position = new Vector([$this->position->components()[0], 0]);
        }
        if ($this->position->components()[1] < 0) {
            $this->position = new Vector([$this->position->components()[0], Simulation::HEIGHT]);
        }

        return true;
    }

    /**
     * @param Vector[] $mines
     *
     * @return Vector
     */
    public function getClosestMine(array $mines)
    {
        $closestSoFar = 99999;
        $closestObject = Vector::nullVector(2);

        // cycle through mines to find closest
        for ($i = 0; $i < count($mines); $i++) {
            $distanceToObject = $mines[$i]->subtract($this->position)->length();

            if ($distanceToObject < $closestSoFar) {
                $closestSoFar = $distanceToObject;
                $closestObject = $this->position->subtract($mines[$i]);
                $this->closestMine = $i;
            }
        }

        return $closestObject;
    }

    /**
     * @return Vector
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'position' => ['x' => $this->position->components()[0], 'y' => $this->position->components()[1]],
            'fitness' => $this->fitness,
        ];
    }
}
