<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Waffle\Application;
use Waffle\DependencyInjection\ContainerCompiler;
use Waffle\Model\IO\IOStyle;

// Load and compile the DI container.
$compiler = new ContainerCompiler();
$container = $compiler->getContainer();

$io = $container->get(IOStyle::class);

try {
  $application = $container->get(Application::class);
  $application->run();
} catch (\Exception $e) {
  $io->error($e->getMessage());
}
