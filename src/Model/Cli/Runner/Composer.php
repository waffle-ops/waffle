<?php

namespace Waffle\Model\Cli\Runner;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliCommand;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\ComposerCommand;

class Composer extends BaseCliRunner
{

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

        $command = new ComposerCommand(
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

        $command = new ComposerCommand(
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
        $command = new BaseCliCommand(['grep', '-v', '!']);
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

        $command = new ComposerCommand(
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
     * @return Process
     * @throws Exception
     */
    public function install(): Process
    {
        $command = new ComposerCommand(
            [
                'install',
            ]
        );

        return $command->getProcess();
    }
}
