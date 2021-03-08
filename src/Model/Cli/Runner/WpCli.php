<?php

namespace Waffle\Model\Cli\Runner;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\BaseCliCommand;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\WpCliCommand;

class WpCli extends BaseCliRunner
{

    /**
     * Checks if WP CLI is installed.
     *
     * @return bool
     * @throws Exception
     */
    public function isInstalled(): bool
    {
        // @todo: run this on construct and/or cache the result?

        $command = new BaseCliCommand(['which', 'wp']);
        $process = $command->getProcess();
        $cliHelper = new CliHelper();
        $output = $cliHelper->getOutput($process);
        return !empty($output);
    }

    /**
     * Gets the current WP core version.
     *
     * @return string
     * @throws Exception
     */
    public function coreVersion(): string
    {
        $command = new WpCliCommand(
            [
                'core',
                'version',
            ]
        );

        $cliHelper = new CliHelper();
        return trim($cliHelper->getOutput($command->getProcess(), true, false));
    }

    /**
     * Checks for any updates for Wordpress core.
     *
     * @param string $format
     *
     * @return Process
     * @throws Exception
     */
    public function coreCheckUpdate($format = 'table'): Process
    {
        // @todo: table output is not showing table borders.
        $command = new WpCliCommand(
            [
                'core',
                'check-update',
                "--format={$format}",
            ]
        );

        return $command->getProcess();
    }

    /**
     * Gets a list of available plugin updates.
     *
     * @param string $format
     *
     * @return Process
     * @throws Exception
     */
    public function pluginListAvailable($format = 'table'): Process
    {
        // @todo: table output is not showing table borders.
        $command = new WpCliCommand(
            [
                'plugin',
                'list',
                '--fields=name,status,version,update_version',
                '--update=available',
                "--format={$format}",
            ]
        );

        return $command->getProcess();
    }

    /**
     * Gets a list of available theme updates.
     *
     * @param string $format
     *
     * @return Process
     * @throws Exception
     */
    public function themeListAvailable($format = 'table'): Process
    {
        // @todo: table output is not showing table borders.
        $command = new WpCliCommand(
            [
                'theme',
                'list',
                '--fields=name,status,version,update_version',
                '--update=available',
                "--format={$format}",
            ]
        );

        return $command->getProcess();
    }

    //wp theme list --fields="name,status,version,update_version" --update="available"

    /**
     * Updates core, plugin, or theme.
     *
     * @param $name
     * @param $version
     *
     * @throws Exception
     * @return mixed
     */
    public function updatePackage($name, $type, $version = null): Process
    {
        switch ($type) {
            case "core":
                return $this->coreUpdate($version);
                break;
            case "plugin":
                return $this->pluginUpdate($name);
                break;
            case "theme":
                return $this->themeUpdate($name);
                break;
        }
        return false;
    }


    /**
     * Updates Wordpress core.
     *
     * @param null $version
     *
     * @return Process
     * @throws Exception
     */
    public function coreUpdate($version = null): Process
    {
        $args = [
            'core',
            'update',
        ];

        if (!empty($version)) {
            $args[] = "--version={$version}";
        }

        $command = new WpCliCommand($args);

        return $command->getProcess();
    }

    /**
     * Updates a Wordpress plugin by name.
     *
     * @param $name
     *
     * @throws Exception
     * @return Process
     */
    public function pluginUpdate($name): Process
    {
        $command = new WpCliCommand(
            [
                'plugin',
                'update',
                $name,
            ]
        );

        return $command->getProcess();
    }

    /**
     * Updates a Wordpress theme by name.
     *
     * @param $name
     *
     * @throws Exception
     * @return Process
     */
    public function themeUpdate($name): Process
    {
        $command = new WpCliCommand(
            [
                'theme',
                'update',
                $name,
            ]
        );

        return $command->getProcess();
    }

    /**
     * Clears the Wordpress cache.
     *
     * @throws Exception
     * @return Process
     */
    public function cacheFlush(): Process
    {
        $command = new WpCliCommand(
            [
                'cache',
                'flush',
            ]
        );

        return $command->getProcess();
    }

    /**
     * Updates Wordpress database.
     *
     * @throws Exception
     * @return Process
     */
    public function updateDatabase(): Process
    {
        $command = new WpCliCommand(
            [
                'core',
                'update-db',
            ]
        );

        return $command->getProcess();
    }
}
