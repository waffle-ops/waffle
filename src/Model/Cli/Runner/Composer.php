<?php

namespace Waffle\Model\Cli\Runner;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\Factory\ComposerCommandFactory;
use Waffle\Model\Cli\Factory\GenericCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Composer extends BaseCliRunner
{

    /**
     * @var ComposerCommandFactory
     */
    private $composerCommandFactory;

    /**
     * @var GenericCommandFactory
     */
    private $genericCommandFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param ComposerCommandFactory $composerCommandFactory
     * @param GenericCommandFactory $genericCommandFactory
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        ComposerCommandFactory $composerCommandFactory,
        GenericCommandFactory $genericCommandFactory
    ) {
        $this->composerCommandFactory = $composerCommandFactory;
        $this->genericCommandFactory = $genericCommandFactory;
        parent::__construct($context, $io);
    }

    /**
     * Runs composer outdated to retrieve only minor version updates.
     *
     * @param string $directory
     * @param string $format
     *
     * @return Process
     * @throws Exception
     */
    public function getMinorVersionUpdates($directory = '', $format = 'text'): Process
    {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'outdated',
                '-Dmn',
                '--strict',
                '--no-ansi',
                "--working-dir={$directory}",
                "--format={$format}",
                '*/*',
            ]
        );

        return $command->getProcess();
    }

    /**
     * Runs composer outdated to retrieve only major version updates.
     *
     * @param string $directory
     *
     * @return Process
     * @throws Exception
     */
    public function getMajorVersionUpdates($directory = ''): Process
    {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'outdated',
                '-Dn',
                '--strict',
                '--no-ansi',
                "--working-dir={$directory}",
                '*/*',
            ]
        );

        $process = $command->getProcess();
        $process->run();
        $output = $process->getOutput();

        // Filter out non-major updates.
        $command = $this->genericCommandFactory->create(['grep', '-v', '!']);
        $process = $command->getProcess();
        $process->setInput($output);

        return $process;
    }

    /**
     * Update a composer package.
     *
     * @param $package
     * @param $timeout
     * @param string $directory
     *
     * @return Process
     * @throws Exception
     */
    public function updatePackage($package, $timeout, $directory = ''): Process
    {
        if (empty($package)) {
            throw new Exception(
                'You must pass a package name to update.'
            );
        }

        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'update',
                '--with-dependencies',
                '--no-ansi',
                '-n',
                "--working-dir={$directory}",
                $package,
            ]
        );

        $process = $command->getProcess();

        if (isset($timeout)) {
            $process->setTimeout($timeout);
        }

        return $process;
    }

    /**
     * Install composer dependencies.
     *
     * @param string $directory
     * @return Process
     */
    public function install(string $directory = ''): Process
    {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'install',
                "--working-dir={$directory}",
            ]
        );

        return $command->getProcess();
    }

    /**
     * Gets the composer.json path.
     *
     * @return string
     */
    public static function determineComposerPath()
    {
        // @todo: use Finder here instead.
        $cwd = getcwd();

        // Current directory.
        $composer_path = $cwd . '/composer.json';
        if (file_exists($composer_path)) {
            return './';
        }

        // Parent directory.
        $composer_path = $cwd . '/../composer.json';
        if (file_exists($composer_path)) {
            return '../';
        }

        return false;
    }
}
