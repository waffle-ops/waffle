<?php

namespace Waffle\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Waffle\Application;

class TestCase extends PHPUnitTestCase
{

    /**
     * Gets the service container.
     *
     * This should not be needed, but there is no official way of setting up
     * and using the container in a standalone console application like Waffle.
     * The online documentation indicates we could use a KernelTestCase but,
     * again, since we are not a full blow Syfony application, we are missing
     * out on some goodies. It does not seem like the maintainers are willing
     * to make a CLI based test layer, so we are making our own.
     *
     * See https://symfony.com/doc/current/console.html#testing-commands
     * See https://github.com/symfony/symfony/issues/27479
     *
     * @return ContainerBuilder
     */
    protected static function getContainer(): ContainerBuilder
    {
        // The container is created and compiled in tests/bootstrap.php
        global $container;
        return $container;
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
