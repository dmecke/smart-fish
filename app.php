<?php

require __DIR__.'/vendor/autoload.php';

use SmartFish\Command\ServerCommand;

$app = new \Symfony\Component\Console\Application();
$app->add(new ServerCommand());
$app->run();
