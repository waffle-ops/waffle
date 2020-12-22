<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Waffle\Application;
use Waffle\Model\Command\CommandManager;

// Load and compile the DI container.
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator());
$loader->load(__DIR__ . '/../config/services.yml');
$container->compile();

// Loading the application from the container to take advantage of the ability
// to inject the commands in the DI layer.
// $application = $container->get(Application::class);
$commandManager = $container->get(CommandManager::class);
$application = new Application($commandManager);
$application->run();
