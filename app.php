<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use SmartFish\Server;

require __DIR__.'/vendor/autoload.php';

$server = new Server();

$loop = Factory::create();
$loop->addPeriodicTimer(0.01, function() use ($server) { $server->update(); });

$socketServer = new SocketServer($loop);
$socketServer->listen(8080, '0.0.0.0');

$ioServer = new IoServer(
    new HttpServer(new WsServer($server)),
    $socketServer,
    $loop
);

$loop->run();
