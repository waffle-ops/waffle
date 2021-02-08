<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Waffle\Application;
use Waffle\Model\Command\CommandManager;
use Waffle\Exception\Config\MissingConfigFileException;

// Load and compile the DI container.
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator());
$loader->load(__DIR__ . '/../config/services.yml');
$container->compile();

$application = $container->get(Application::class);

try {
    // Loading the command manager from the container to take advantage of the
    // ability to inject the commands in the DI layer.
    $commandManager = $container->get(CommandManager::class);
    $application->setCommandManager($commandManager);
} catch (MissingConfigFileException $e) {
    // Intentionally blank. If no config is present, this is expected.
} catch (\Exception $e) {
    throw $e;
}

$application->run();
