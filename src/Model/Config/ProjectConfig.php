<?php

namespace Waffle\Model\Config;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;
use Waffle\Model\Output\Runner;

class ProjectConfig
{

    /**
     * @var ProjectConfig
     *
     * $instance The ProjectConfig instance.
     */
    private static $instance = null;

    /**
     * @var array
     *
     * $project_config The project configuration
     */
    private $project_config = [];
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->loadProjectConfig();
    }
    
    /**
     * Gets the ProjectConfig singleton.
     *
     * @return ProjectConfig
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ProjectConfig();
        }
    
        return self::$instance;
    }

    /**
     * Gets the $project_config array.
     *
     * @return array
     */
    public function getProjectConfig()
    {
        return $this->project_config;
    }

    /**
     * Loads the $project_config array.
     *
     * @return void
     */
    private function loadProjectConfig()
    {
        $project_config_file = $this->getProjectConfigPath();
        $this->project_config = [];
        if (!empty($project_config_file)) {
            $this->project_config = Yaml::parseFile($project_config_file);
            $this->project_config['config_path'] = str_replace('.waffle.yml', '', $project_config_file);
        } else {
            $output = new ConsoleOutput();
            $output->writeln('<error>Unable to find .waffle.yml - Falling back to derived defaults.</error>');
        }
    
        if (empty($this->project_config['config_path'])) {
            $this->project_config['config_path'] = getcwd();
        }
        
        $this->setProjectConfigDefaults();
    }

    /**
     * Gets the project config path.
     *
     * @return string
     */
    private function getProjectConfigPath()
    {
        // For initial launch, we will only check the current directory (assuming
        // docroot) and the immediate parent directory.
        $cwd = getcwd();

        // Current directory.
        $project_config_file = $cwd . '/.waffle.yml';
        if (file_exists($project_config_file)) {
            return $project_config_file;
        }
    
        // Parent directory.
        $project_config_file = $cwd . '/../.waffle.yml';
        if (file_exists($project_config_file)) {
            return $project_config_file;
        }
        
        return false;
    }
    
    /**
     * If config is not explicitly set, then define some defaults from info that
     * can be derived from project structure and environment.
     */
    private function setProjectConfigDefaults()
    {
        // Attempt to derive the composer.json path.
        if (!isset($this->project_config['composer_path'])) {
            $composer_path = $this->getComposerPath();
            if (!empty($composer_path)) {
                $this->project_config['composer_path'] = $composer_path;
            }
        }
        
        // Attempt to see if the Symfony CLI is installed.
        if (!isset($this->project_config['symfony_cli'])) {
            $output = Runner::getOutput('which symfony');
            if (!empty($output)) {
                $this->project_config['symfony_cli'] = $output;
            }
        }
        
        // Attempt to determine the Drush minor and major versions.
        $drush_version = Runner::getOutput('drush version --format=string');
        if (!isset($this->project_config['drush_version'])) {
            if (!empty($drush_version)) {
                $this->project_config['drush_version'] = $drush_version;
            }
        }
    
        if (!isset($this->project_config['drush_major_version'])) {
            $drush_major_version = explode('.', $this->project_config['drush_version'])[0];
            if (!empty($drush_major_version)) {
                $this->project_config['drush_major_version'] = $drush_major_version;
            }
        }
    
        
        // @todo: define and derive other config defaults based on project files.
    }
    
    /**
     * Gets the composer.json path.
     *
     * @return string
     */
    private function getComposerPath()
    {
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
