<?php

namespace Waffles\Model\Config;

use Symfony\Component\Yaml\Yaml;

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
        $this->project_config = Yaml::parseFile($project_config_file);
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
