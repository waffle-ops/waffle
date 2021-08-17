<?php

namespace Waffle\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Waffle\Application;

class TestCase extends PHPUnitTestCase
{

    /**
     * The service container.
     *
     * @var ContainerBuilder
     */
    private static $container;

    /**
     * Gets the service container.
     *
     * There is no official way of setting upand using the container in a
     * standalone console application like Waffle. The online documentation
     * indicates we could use a KernelTestCase but since we are not a full
     * blown Symfony application, we are missing out on some goodies.
     *
     * It does not seem like the maintainers are willing to make a CLI based
     * test layer, so we are using this as a workaround.
     *
     * See https://symfony.com/doc/current/console.html#testing-commands
     * See https://github.com/symfony/symfony/issues/27479
     *
     * @return ContainerBuilder
     */
    protected static function getContainer(): ContainerBuilder
    {
        if (empty(static::$container)) {
            static::$container = new ContainerBuilder();
            $loader = new YamlFileLoader(static::$container, new FileLocator());
            $loader->load(__DIR__ . '/../../config/services.yml');
            static::$container->compile();
        }

        return static::$container;
    }

    /**
     * Gets an instance of the provided class from the container.
     *
     * @param string $clazz
     *   The class to load from the container.
     *
     * @return mixed
     */
    protected static function getSystemUnderTest($clazz)
    {
        $container = static::getContainer();
        return $container->get($clazz);
    }

    /**
     * Gets an instance of the Waffle application.
     *
     * @return Application
     */
    protected static function getApplication(): Application
    {
        $container = static::getContainer();
        return $container->get(Application::class);
    }

    /**
     * Gets a command testing object that is ready to run tests against.
     *
     * @param string $command_name
     *   The name of the command to test.
     *
     * @return CommandTester
     */
    protected static function getCommandTester(string $command_name): CommandTester
    {
        $application = static::getApplication();
        $command = $application->find($command_name);
        return new CommandTester($command);
    }
}
