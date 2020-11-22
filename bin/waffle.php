<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Waffle\Application;
use Waffle\Model\IO\IO;

$io = IO::getInstance();

$app = new Application();
$app->run($io->getInput(), $io->getOutput());
