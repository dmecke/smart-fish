<?php

namespace SmartFish\Command;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use SmartFish\System\Output;
use SmartFish\System\Server;
use SmartFish\Simulation\Simulation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('smartfish:run');
        $this->setDescription('Runs the Smart Fish server.');
        $this->addOption('fps', null, InputOption::VALUE_OPTIONAL, 'Frames per second to calculate', 30);
        $this->addOption('ticks-per-generation', null, InputOption::VALUE_OPTIONAL, 'Ticks to calculate for every generation', 2000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = new Server(new Simulation(new Output($output), $input->getOption('ticks-per-generation')));

        $fps = (int) $input->getOption('fps');

        $loop = Factory::create();
        $loop->addPeriodicTimer(1 / $fps, function() use ($server) { $server->update(); });

        $socketServer = new SocketServer($loop);
        $socketServer->listen(8080, '0.0.0.0');

        new IoServer(
            new HttpServer(new WsServer($server)),
            $socketServer,
            $loop
        );

        $loop->run();
    }
}
