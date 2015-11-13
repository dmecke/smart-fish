<?php

class UtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_leaves_values_between_min_and_max_untouched()
    {
        $this->assertSame(3.0, Util::clamp(3.0, 1.0, 5.0));
    }

    /**
     * @test
     */
    public function it_returns_the_minimum_for_smaller_values()
    {
        $this->assertSame(1.0, Util::clamp(0.5, 1.0, 5.0));
    }

    /**
     * @test
     */
    public function it_returns_the_maxium_for_bigger_values()
    {
        $this->assertSame(5.0, Util::clamp(0.5, 6.0, 5.0));
    }
}
