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
     * @var array List of direct dependencies (static cache).
     */
    private static $directDependencies;

    /**
     * @var array Full outdated package list information (static cache).
     */
    private static $fullOutdatedInfo;

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
     * @param bool $onlyDirect
     *
     * @return Process
     */
    public function getMinorVersionUpdates(
        string $format = 'text',
        bool $onlyDirect = true,
        string $packageName = '',
        string $directory = ''
    ): Process {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $flags = '-Dmn';
        if (!$onlyDirect) {
            $flags = '-mn';
        }

        if (empty($packageName)) {
            $packageName = '*/*';
        }

        $command = $this->composerCommandFactory->create(
            [
                'outdated',
                $flags,
                '--strict',
                '--no-ansi',
                "--working-dir={$directory}",
                "--format={$format}",
                $packageName,
            ]
        );

        return $command->getProcess();
    }

    /**
     * Get the list of direct minor version updates, formatted for use as a table.
     *
     * @param string $directory
     *
     * @return array
     */
    public function getMinorVersionUpdatesTable(string $directory = ''): array
    {
        $process = $this->getMinorVersionUpdates('json', true, '', $directory);
        return $this->convertOutdatedToTable($process);
    }

    /**
     * Get the list of secondary minor version updates, formatted for use as a table.
     *
     * @param string $directory
     *
     * @return array
     * @throws Exception
     */
    public function getMinorSecondaryVersionUpdatesTable(string $packageName = '', string $directory = ''): array
    {
        $direct = $this->getDirectDependencyList();
        $process = $this->getMinorVersionUpdates('json', false, $packageName, $directory);
        $all = $this->convertOutdatedToTable($process);

        $secondary = [
            'headers' => $all['headers'],
            'rows' => [],
        ];
        $secondary['headers'][] = 'Parent(s)';

        foreach ($all['rows'] as $name => $package) {
            if (in_array($name, $direct)) {
                continue;
            }

            // Ignore anything not required by a root package (ie: composer/composer, psr/*).
            $parents = $this->getParentDependencyNames($name);
            $parents = $this->filterNonDirectDependencyNames($parents);

            if (empty($parents)) {
                continue;
            }

            $secondary['rows'][$name] = $package;
            $secondary['rows'][$name]['parents'] = implode(', ', $parents);
        }

        return $secondary;
    }

    /**
     * Gets a list of parent names that require a package.
     *
     * @param $packageName
     * @param string $directory
     *
     * @return array
     */
    public function getParentDependencyNames($packageName, string $directory = ''): array
    {
        $process = $this->why($packageName, $directory);
        $process->run();
        $output = $process->getOutput();

        // Composer does not provide a json format of this list, so we must parse it ourselves.
        $lines = explode("\n", $output);
        $parents = [];
        foreach ($lines as $line) {
            $columns = explode(' ', $line);
            $parents[] = $columns[0];
        }

        return $parents;
    }

    /**
     * Defines a utility function for removing non-direct dependencies from a list of package names.
     *
     * @param $names
     *
     * @return array
     * @throws Exception
     */
    protected function filterNonDirectDependencyNames($names): array
    {
        $direct = $this->getDirectDependencyList();
        return array_intersect($names, $direct);
    }

    /**
     * Defines a utility function that converts `outdated` output to a CLI table format.
     *
     * @param $process
     * @param callable|null $filterCallback
     *
     * @return array
     */
    protected function convertOutdatedToTable($process, callable $filterCallback = null): array
    {
        $process->run();
        $output = $process->getOutput();
        $raw = json_decode($output, true);

        $table = [
            'headers' => [
                'Package Name',
                'Current Version',
                'New Version',
            ],
            'rows' => [],
        ];

        if (empty($raw['installed'])) {
            return $table;
        }

        foreach ($raw['installed'] as $package) {
            if (!empty($filterCallback) && is_callable($filterCallback)) {
                if (!$filterCallback($package)) {
                    continue;
                }
            }
            $table['rows'][$package['name']] = [
                'name' => $package['name'],
                'version' => $package['version'],
                'latest' => $package['latest'],
            ];
        }

        return $table;
    }

    /**
     * Gets a list of all direct required dependency package names in the project's composer.json
     *
     * @param string $directory
     *
     * @return int[]|string[]
     * @throws Exception
     */
    public function getDirectDependencyList(string $directory = '')
    {
        // Check the static cache.
        if (!empty(static::$directDependencies)) {
            return static::$directDependencies;
        }

        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'info',
                '-s',
                '--format=json',
                '--no-ansi',
                "--working-dir={$directory}",
                '*/*',
            ]
        );

        $process = $command->getProcess();
        $process->run();
        $output = $process->getOutput();
        $raw = json_decode($output, true);

        if (empty($raw['requires'])) {
            throw new Exception("Unable to derive project direct composer requirements.");
        }

        static::$directDependencies = array_keys($raw['requires']);
        return static::$directDependencies;
    }

    /**
     * Runs composer outdated to retrieve only major version updates.
     *
     * @param string $directory
     *
     * @return array
     */
    public function getMajorVersionUpdatesTable(string $directory = ''): array
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
                "--format=json",
                '*/*',
            ]
        );

        $process = $command->getProcess();

        return $this->convertOutdatedToTable(
            $process,
            function ($package) {
                if (empty($package['latest-status'])) {
                    return false;
                }

                if ($package['latest-status'] !== 'update-possible') {
                    return false;
                }

                return true;
            }
        );
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
    public function updatePackage($package, $timeout, string $directory = ''): Process
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
            // @todo: Remove hardcoded timeout and use global timeout instead.
            $process->setTimeout($timeout);
        }

        return $process;
    }

    /**
     * Gets package info related to updates for all packages.
     *
     * @param string $directory
     *
     * @return array
     */
    public function getFullOutdatedInfo(string $directory = '')
    {
        // Check the static cache.
        if (!empty(static::$fullOutdatedInfo)) {
            return static::$fullOutdatedInfo;
        }

        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'outdated',
                '-ma',
                "--format=json",
                "--working-dir={$directory}",
                '*/*',
            ]
        );

        $process = $command->getProcess();
        $process->run();
        $output = $process->getOutput();
        static::$fullOutdatedInfo = json_decode($output, true);
        return static::$fullOutdatedInfo;
    }

    /**
     * Gets information about a composer package.
     *
     * @param $package
     * @param string $directory
     *
     * @return array
     * @throws Exception
     */
    public function getPackageInfo($package, string $directory = ''): array
    {
        if (empty($package)) {
            throw new Exception(
                'You must pass a package name to check.'
            );
        }

        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $outdated_packages = $this->getFullOutdatedInfo($directory);

        $key = array_search($package, array_column($outdated_packages['installed'], 'name'));

        if ($key === false) {
            return [];
        }

        return $outdated_packages['installed'][$key];
    }

    /**
     * Get the currently installed version number for a composer package.
     *
     * @param $package
     * @param string $directory
     *
     * @return string
     * @throws Exception
     */
    public function getPackageInstalledVersion($package, string $directory = ''): string
    {
        $info = $this->getPackageInfo($package);

        if (empty($info['version'])) {
            // @todo: log as error?
            return '';
        }

        return $info['version'];
    }

    /**
     * Install composer dependencies.
     *
     * @param string $directory
     *
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

    public function why(string $packageName, string $directory = ''): Process
    {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->composerCommandFactory->create(
            [
                'why',
                "--working-dir={$directory}",
                '--no-ansi',
                '-n',
                $packageName,
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
