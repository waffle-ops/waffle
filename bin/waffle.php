<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Waffle\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();

$app = new Application();
$app->run(null, $output);
