<?php

namespace SmartFish\System;

use Exception;
use SmartFish\Simulation\Simulation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

class Output
{
    /**
     * @var OutputInterface
     */
    private $output;

    private $bestValuesSoFar = ['average' => 0, 'worst' => 0, 'best' => 0];

    /**
     * @param OutputInterface $outputInterface
     */
    public function __construct(OutputInterface $outputInterface)
    {
        $this->output = $outputInterface;
    }

    /**
     * @param Simulation $simulation
     */
    public function outputGeneration(Simulation $simulation)
    {

        $generation = $simulation->getGeneration();
        $average = floor($simulation->averageFitness());
        $best = floor($simulation->bestFitness());
        $worst = floor($simulation->worstFitness());

        $averageChart = str_repeat('=', $average);
        $bestChart = str_repeat('-', $best - $average);

        $style = new TableStyle();
        $style->setHorizontalBorderChar('')->setCrossingChar('')->setVerticalBorderChar('');

        $table = new Table($this->output);
        $table->setStyle($style);

        $table->setRows([
            [
                $this->prepareValue('generation', $generation, false),
                $this->prepareValue('worst', $worst),
                $this->prepareValue('average', $average),
                $this->prepareValue('best', $best),
                $averageChart . $bestChart,
            ]
        ]);
        $table->render();
    }

    /**
     * @param string $key
     * @param int $value
     * @param bool $highlightRecords
     *
     * @return string
     *
     * @throws Exception
     */
    private function prepareValue($key, $value, $highlightRecords = true)
    {
        if ($highlightRecords && $value > $this->bestValuesSoFar[$key]) {
            $this->bestValuesSoFar[$key] = $value;
            $value = sprintf('<fg=green>%s</>', $value);
        }

        $label = $this->getLabelForKey($key);

        return sprintf($label, $value);
    }

    /**
     * @param string $key
     *
     * @return string
     *
     * @throws Exception
     */
    private function getLabelForKey($key)
    {
        $mapping = [
            'generation' => 'Generation %s',
            'average' => 'Average: %s',
            'best' => 'Best: %s',
            'worst' => 'Worst: %s',
        ];

        if (!array_key_exists($key, $mapping)) {
            throw new Exception(sprintf('could not find key %s in mapping', $key));
        }

        return $mapping[$key];
    }
}
