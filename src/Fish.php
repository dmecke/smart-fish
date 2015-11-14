<?php

use Nubs\Vectorix\Vector;

class Fish implements JsonSerializable
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
    private $turnLeft = 0.16;

    /**
     * @var float
     */
    private $turnRight = 0.16;

    /**
     * @var float
     */
    private $fitness = 0;

    /**
     * @var int
     */
    private $closestFood;

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
     * @param Vector[] $fishes
     *
     * @return int
     */
    public function checkForFood(array $fishes)
    {
        $distance = $this->position->subtract($fishes[$this->closestFood]);

        if ($distance->length() < (Food::SIZE + self::SIZE)) {
            return $this->closestFood;
        }

        return -1;
    }
    
    /**
     * @param Vector[] $food
     * 
     * @return bool
     *
     * @throws \Exception
     *
     * First we take sensor readings and feed these into the fish brain.
     * The inputs are:
     *
     * A vector to the closest food (x, y)
     * The fish 'look at' vector (x, y)
     * We receive two outputs from the brain.. turnLeft & turnRight.
     * So given a force for each side we calculate the resultant rotation
     * and acceleration and apply to current velocity vector.
     */
    public function update(array $food)
    {
        // this will store all the inputs for the NN
        $inputs = [];

        // get vector to closest food
        $closestFood = $this->getClosestFood($food);

        // normalise it
        if ($closestFood->length() != 0) {
            $closestFood = $closestFood->normalize();
        }

        // add in vector to closest food
        $inputs[] = $closestFood->components()[0];
        $inputs[] = $closestFood->components()[1];

        // add in fish look at vector
        $inputs[] = $this->lookAt->components()[0];
        $inputs[] = $this->lookAt->components()[1];


        // update the brain and get feedback
        $output = $this->neuralNet->update($inputs);

        // make sure there were no errors in calculating the output
        if (count($output) < self::BRAIN_OUTPUTS) {
            throw new \Exception(sprintf('number of output values (%s) does not match expected number (%s)', count($output), self::BRAIN_OUTPUTS));
        }

        // assign the outputs to the fish turn left & right
        $this->turnLeft = $output[0];
        $this->turnRight = $output[1];

        // calculate steering forces
        $rotationForce = $this->turnLeft - $this->turnRight;

        // clamp rotation
        $rotationForce = Util::clamp($rotationForce, -self::MAX_TURN_RATE, self::MAX_TURN_RATE);

        $this->rotation += $rotationForce;

        $this->speed = ($this->turnLeft + $this->turnRight);

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
     * @param Vector[] $food
     *
     * @return Vector
     */
    public function getClosestFood(array $food)
    {
        $closestSoFar = 99999;
        $closestObject = Vector::nullVector(2);

        // cycle through food to find closest
        for ($i = 0; $i < count($food); $i++) {
            $distanceToObject = $food[$i]->subtract($this->position)->length();

            if ($distanceToObject < $closestSoFar) {
                $closestSoFar = $distanceToObject;
                $closestObject = $this->position->subtract($food[$i]);
                $this->closestFood = $i;
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
