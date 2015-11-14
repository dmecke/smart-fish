<?php

namespace SmartFish\Genetic;

use PHPUnit_Framework_TestCase;

class GenomeSelectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var GenomeSelector
     */
    private $selector;

    /**
     * @var Genome[]
     */
    private $genomes;

    /**
     * @var Genome
     */
    private $fittestGenome;

    /**
     * @var Genome
     */
    private $secondFittestGenome;

    protected function setUp()
    {
        $this->selector = new GenomeSelector();

        $genome1 = new Genome();
        $genome1->setFitness(1.0);

        $genome2 = new Genome();
        $genome2->setFitness(5.0);

        $genome3 = new Genome();
        $genome3->setFitness(2.0);

        $genome4 = new Genome();
        $genome4->setFitness(3.0);

        $this->genomes = [$genome1, $genome2, $genome3, $genome4];
        $this->fittestGenome = $genome2;
        $this->secondFittestGenome = $genome4;
    }

    /**
     * @test
     */
    public function it_selects_the_fittest_genome_via_elite_seletion()
    {
        $this->assertSame([
            $this->fittestGenome
        ], $this->selector->selectElite($this->genomes, 1, 1));
    }

    /**
     * @test
     */
    public function it_selects_multiple_copies_of_the_fittest_genome_via_elite_selection()
    {
        $this->assertSame([
            $this->fittestGenome, $this->fittestGenome
        ], $this->selector->selectElite($this->genomes, 1, 2));
    }

    /**
     * @test
     */
    public function it_selects_the_two_fittest_genomes_via_elite_selection()
    {
        $this->assertSame([
            $this->secondFittestGenome, $this->fittestGenome
        ], $this->selector->selectElite($this->genomes, 2, 1));
    }

    /**
     * @test
     */
    public function it_selects_multiple_copies_of_the_two_fittest_genomes_via_elite_selection()
    {
        $this->assertSame([
            $this->secondFittestGenome, $this->secondFittestGenome, $this->fittestGenome, $this->fittestGenome
        ], $this->selector->selectElite($this->genomes, 2, 2));
    }
}
