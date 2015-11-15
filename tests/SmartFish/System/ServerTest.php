<?php

namespace SmartFish\System;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Ratchet\ConnectionInterface;
use SmartFish\Simulation\Simulation;

class ServerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Server
     */
    private $server;

    public function setUp()
    {
        $simulation = $this->createSimulationMock();
        $connection1 = $this->createConnectionMock();
        $connection2 = $this->createConnectionMock();

        $this->server = new Server($simulation);
        $this->server->onOpen($connection1);
        $this->server->onOpen($connection2);
    }

    /**
     * @test
     */
    public function it_sends_a_message_to_all_connections_on_update()
    {
        $this->server->update();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ConnectionInterface
     */
    private function createConnectionMock()
    {
        $connection = $this->getMockBuilder(ConnectionInterface::class)->setMethods(['send', 'close'])->getMock();
        $connection->expects($this->once())->method('send');

        return $connection;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Simulation
     */
    private function createSimulationMock()
    {
        $simulation = $this->getMockBuilder(Simulation::class)->disableOriginalConstructor()->setMethods(['update'])->getMock();
        $simulation->expects($this->once())->method('update');

        return $simulation;
    }
}
