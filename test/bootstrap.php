<?php

$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    throw new RuntimeException('Install dependencies to run phpunit.');
}
$loader = require_once $autoloadFile;
$loader->add('Slim\\Controller\\', __DIR__);
