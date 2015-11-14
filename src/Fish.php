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
     * @var float
     */
    private $rotation;

    /**
     * @var float
     */
    private $fitness = 0.0;

    /**
     * @var int
     */
    private $closestFoodIndex;

    public function __construct()
    {
        $this->reset();
        $this->neuralNet = new NeuralNet(self::BRAIN_INPUTS, self::BRAIN_OUTPUTS, self::BRAIN_HIDDEN_LAYERS, self::BRAIN_NEURONS_PER_HIDDEN_LAYER);
    }

    public function reset()
    {
        $this->position = new Vector([mt_rand(0, Simulation::WIDTH), mt_rand(0, Simulation::HEIGHT)]);
        $this->rotation = rand(0, 100) * pi() * 2;
        $this->fitness = 0;
    }

    /**
     * @param Food $food
     */
    public function eat(Food $food)
    {
        $this->fitness += $food->getNutritionalValue();
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
     * @param Food[] $foods
     *
     * @return int
     */
    public function checkForFood(array $foods)
    {
        $distance = $this->position->subtract($foods[$this->closestFoodIndex]->getPosition());

        if ($distance->length() < (Food::SIZE + self::SIZE)) {
            return $this->closestFoodIndex;
        }

        return -1;
    }
    
    /**
     * @param Food[] $food
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
        // get vector to closest food
        $closestFoodPosition = $this->getClosestFoodVector($food);

        // normalise it
        if ($closestFoodPosition->length() != 0) {
            $closestFoodPosition = $closestFoodPosition->normalize();
        }

        // this will store all the inputs for the NN
        $inputs = [];

        // add in vector to closest food
        $inputs[] = $closestFoodPosition->components()[0];
        $inputs[] = $closestFoodPosition->components()[1];

        // add in fish look at vector
        $inputs[] = $this->lookAt()->components()[0];
        $inputs[] = $this->lookAt()->components()[1];

        // update the brain and get feedback
        $output = $this->neuralNet->update($inputs);

        // make sure there were no errors in calculating the output
        if (count($output) < self::BRAIN_OUTPUTS) {
            throw new \Exception(sprintf('number of output values (%s) does not match expected number (%s)', count($output), self::BRAIN_OUTPUTS));
        }

        // assign the outputs to the fish turn left & right
        $turnLeft = $output[0];
        $turnRight = $output[1];

        // calculate steering forces
        $rotationForce = $turnLeft - $turnRight;

        // clamp rotation
        $rotationForce = Util::clamp($rotationForce, -self::MAX_TURN_RATE, self::MAX_TURN_RATE);

        $this->rotation += $rotationForce;

        $speed = $turnLeft + $turnRight;

        // update Look At
        $this->lookAt();

        // update position
        $this->position = $this->position->add($this->lookAt()->multiplyByScalar($speed));

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
     * @param Food[] $food
     *
     * @return Vector
     */
    public function getClosestFoodVector(array $food)
    {
        $closestSoFar = 99999;
        $closestFoodVector = Vector::nullVector(2);

        // cycle through food to find closest
        for ($i = 0; $i < count($food); $i++) {
            $distanceToObject = $food[$i]->getPosition()->subtract($this->position)->length();

            if ($distanceToObject < $closestSoFar) {
                $closestSoFar = $distanceToObject;
                $closestFoodVector = $this->position->subtract($food[$i]->getPosition());
                $this->closestFoodIndex = $i;
            }
        }

        return $closestFoodVector;
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

    /**
     * @return Vector
     */
    private function lookAt()
    {
        return new Vector([-sin($this->rotation), cos($this->rotation)]);
    }
}
