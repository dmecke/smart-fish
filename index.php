<?php

require __DIR__.'/vendor/autoload.php';

$controller = new Simulation();

while (true) {
    $controller->Update();
}
