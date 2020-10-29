<?php

namespace Waffles;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Waffles\Command\CommandManager;
use Symfony\Component\Yaml\Yaml;

class Application extends SymfonyApplication
{
    public const NAME = 'Waffles';
    public const VERSION = '1.0.0-beta';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $command_manager = new CommandManager();
        $this->addCommands($command_manager->getCommands());

        // TODO Add user defined commands? Or should it be kept to build targets only?
        // TODO Add user defined dependencies here so that we can check that they are there.

        // $this->loadProjectConfig();

        $project_config = $this->getProjectConfig();

        parent::run();
    }

    public function getProjectConfig()
    {
        $project_config_file = $this->getProjectConfigPath();
        $project_config = Yaml::parseFile($project_config_file);

        return $project_config;
    }

    private function getProjectConfigPath()
    {
        // For initial launch, we will only check the current directory (assuming
        // docroot) and the immediate parent directory.
        $cwd = getcwd();

        // Current directory.
        $project_config_file = $cwd . '/.waffles.yml';
        if (file_exists($project_config_file)) {
            return $project_config_file;
        }

        // Parent directory.
        $project_config_file = $cwd . '/../.waffles.yml';
        if (file_exists($project_config_file)) {
            return $project_config_file;
        }

        throw new \Exception('Unable to find .waffles.yml file.');
    }
}
