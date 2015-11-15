<?php

namespace SmartFish\System;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SmartFish\Simulation\Simulation;

class Server implements MessageComponentInterface
{
    /**
     * @var Simulation
     */
    private $simulation;

    /**
     * @var ArrayCollection|ConnectionInterface[]
     */
    private $connections;

    /**
     * @param Simulation $simulation
     */
    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
        $this->connections = new ArrayCollection();
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->connections->add($connection);
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->connections->removeElement($connection);
    }

    /**
     * @param ConnectionInterface $connection
     * @param Exception $e
     *
     * @throws Exception
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        throw $e;
    }

    /**
     * @param ConnectionInterface $from
     * @param string $message
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
    }

    public function update()
    {
        $this->simulation->update();

        foreach ($this->connections as $connection) {
            $connection->send(json_encode($this->simulation));
        }
    }
}
