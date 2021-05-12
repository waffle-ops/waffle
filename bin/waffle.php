<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Waffle\Application;
use Waffle\Model\IO\IOStyle;

// Load and compile the DI container.
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator());
$loader->load(__DIR__ . '/../config/services.yml');
$container->compile();

$io = $container->get(IOStyle::class);

try {
  $application = $container->get(Application::class);
  $application->run();
} catch (\Exception $e) {
  $io->error($e->getMessage());
}
