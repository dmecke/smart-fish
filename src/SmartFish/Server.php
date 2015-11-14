<?php

namespace SmartFish;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Server implements MessageComponentInterface
{
    /**
     * @var Simulation
     */
    private $simulation;

    /**
     * @var ConnectionInterface[]
     */
    private $connections;

    public function __construct()
    {
        $this->simulation = new Simulation();
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
