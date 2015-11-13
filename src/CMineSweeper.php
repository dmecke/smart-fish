<?php

class CMineSweeper
{
    /**
     * @var CNeuralNet
     */
    private $m_ItsBrain;

    /**
     * @var SVector2D
     */
    private $m_vPosition;

    /**
     * @var SVector2D
     */
    private $m_vLookAt;

    /**
     * @var float
     */
    private $m_dRotation;

    /**
     * @var float
     */
    private $m_dSpeed;

    /**
     * @var float
     */
    private $m_lTrack = 0.16;

    /**
     * @var float
     */
    private $m_rTrack = 0.16;

    /**
     * @var float
     */
    private $m_dFitness = 0;

    /**
     * @var float
     */
    private $m_dScale;

    /**
     * @var int
     */
    private $m_iClosestMine;

    public function __construct()
    {
        $this->m_dRotation = rand(0, 100) * CParams::dTwoPi;
        $this->m_dScale = CParams::iSweeperScale;
        $this->m_vPosition = new SVector2D(mt_rand(0, CParams::WindowWidth), mt_rand(0, CParams::WindowHeight));
        $this->m_vLookAt = new SVector2D(0, 0);
        $this->m_ItsBrain = new CNeuralNet();
    }

    public function Reset()
    {
        $this->m_vPosition = new SVector2D(mt_rand(0, CParams::WindowWidth), mt_rand(0, CParams::WindowHeight));
        $this->m_dFitness = 0;
        $this->m_dRotation = rand(0, 100) * CParams::dTwoPi;
    }

    public function IncrementFitness()
    {
        $this->m_dFitness++;
    }

    /**
     * @return float
     */
    public function Fitness()
    {
        return $this->m_dFitness;
    }

    /**
     * @return int
     */
    public function GetNumberOfWeights()
    {
        return $this->m_ItsBrain->GetNumberOfWeights();
    }

    /**
     * @param float[] $w
     */
    public function PutWeights(array $w)
    {
        $this->m_ItsBrain->PutWeights($w);
    }

    /**
     * @param SVector2D[] $mines
     * @param float $size
     *
     * @return int
     */
    public function CheckForMine(array $mines, $size)
    {
        $DistToObject = $this->m_vPosition->subtract($mines[$this->m_iClosestMine]);

        if (SVector2D::Vec2DLength($DistToObject) < ($size + 5)) {
            return $this->m_iClosestMine;
        }

        return -1;
    }
    
    /**
     * @param SVector2D[] $mines
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
    public function Update(array $mines)
    {
        // this will store all the inputs for the NN
        $inputs = [];

        // get vector to closest mine
        $vClosestMine = $this->GetClosestMine($mines);

        // normalise it
        SVector2D::Vec2DNormalize($vClosestMine);

        // add in vector to closest mine
        $inputs[] = $vClosestMine->x;
        $inputs[] = $vClosestMine->y;

        // add in sweepers look at vector
        $inputs[] = $this->m_vLookAt->x;
        $inputs[] = $this->m_vLookAt->y;


        // update the brain and get feedback
        $output = $this->m_ItsBrain->Update($inputs);

        // make sure there were no errors in calculating the output
        if (count($output) < CParams::iNumOutputs) {
            throw new \Exception(sprintf('number of output values (%s) does not match expected number (%s)', count($output), CParams::iNumOutputs));
        }

        // assign the outputs to the sweepers left & right tracks
        $this->m_lTrack = $output[0];
        $this->m_rTrack = $output[1];

        // calculate steering forces
        $RotForce = $this->m_lTrack - $this->m_rTrack;

        // clamp rotation
        $RotForce = Utils::Clamp($RotForce, -CParams::dMaxTurnRate, CParams::dMaxTurnRate);

        $this->m_dRotation += $RotForce;

        $this->m_dSpeed = ($this->m_lTrack + $this->m_rTrack);

        // update Look At
        $this->m_vLookAt->x = -sin($this->m_dRotation);
        $this->m_vLookAt->y = cos($this->m_dRotation);

        // update position
        $this->m_vPosition = $this->m_vPosition->add($this->m_vLookAt->multiply($this->m_dSpeed));

        // wrap around window limits
        if ($this->m_vPosition->x > CParams::WindowWidth) {
            $this->m_vPosition->x = 0;
        }
        if ($this->m_vPosition->x < 0) {
            $this->m_vPosition->x = CParams::WindowWidth;
        }
        if ($this->m_vPosition->y > CParams::WindowHeight) {
            $this->m_vPosition->y = 0;
        }
        if ($this->m_vPosition->y < 0) {
            $this->m_vPosition->y = CParams::WindowHeight;
        }

        return true;
    }

    /**
     * @param SVector2D[] $mines
     *
     * @return SVector2D
     */
    public function GetClosestMine(array $mines)
    {
        $closest_so_far = 99999;
        $vClosestObject = new SVector2D(0, 0);

        // cycle through mines to find closest
        for ($i = 0; $i < count($mines); $i++) {
            $len_to_object = SVector2D::Vec2DLength($mines[$i]->subtract($this->m_vPosition));

            if ($len_to_object < $closest_so_far) {
                $closest_so_far	= $len_to_object;

                $vClosestObject	= $this->m_vPosition->subtract($mines[$i]);

                $this->m_iClosestMine = $i;
            }
        }

        return $vClosestObject;
    }

    /**
     * @return SVector2D
     */
    public function getMVPosition()
    {
        return $this->m_vPosition;
    }
}
