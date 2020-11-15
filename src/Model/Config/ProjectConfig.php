<?php

namespace Waffle\Model\Config;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Waffle\Exception\Config\AmbiguousConfigException;
use Waffle\Exception\Config\MissingConfigFileException;
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
     * @var string
     *
     * Constant for config file name.
     */
    public const CONFIG_FILE = '.waffle.yml';

    /**
     * Constants for referencing keys in the config file.
     */
    public const KEY_ALIAS = 'alias';
    public const KEY_CMS = 'cms';
    public const KEY_DEFAULT_UPSTREAM = 'default_upstream';
    public const KEY_HOST = 'host';
    public const KEY_RECIPES = 'recipes';
    public const KEY_TASKS = 'tasks';
    public const KEY_UPSTREAMS = 'upstreams';

    /**
     * @var array
     *
     * $allowed_keys The allowed top level keys of the config file.
     */
    private $allowed_keys = [
        self::KEY_ALIAS,
        self::KEY_CMS,
        self::KEY_DEFAULT_UPSTREAM,
        self::KEY_HOST,
        self::KEY_RECIPES,
        self::KEY_TASKS,
        self::KEY_UPSTREAMS,
    ];

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
        trigger_error('Warning: ProjectConfig::getProjectConfig() is deprecated. Use access methods instead.');
        return $this->project_config;
    }

    /**
     * Loads the $project_config array (if possible).
     *
     * @throws MissingConfigFileException
     * @throws AmbiguousConfigException
     *
     * @return void
     */
    private function loadProjectConfig()
    {
        $project_config_file = $this->getProjectConfigPath();

        $this->project_config = [];
        $this->project_config = Yaml::parseFile($project_config_file);
        $this->project_config['config_path'] = str_replace('.waffle.yml', '', $project_config_file);

        $this->setProjectConfigDefaults();
    }

    /**
     * Gets the project config path.
     *
     * @throws MissingConfigFileException
     * @throws AmbiguousConfigException
     *
     * @return string
     */
    private function getProjectConfigPath()
    {
        // For initial launch, we will only check the current directory (assuming
        // docroot) and the immediate parent directory.
        $finder = new Finder();
        $finder->ignoreDotFiles(false);
        $finder->files();
        $finder->in([
            getcwd(),
            dirname(getcwd() . '..'),
        ]);
        $finder->depth('== 0');
        $finder->name(self::CONFIG_FILE);

        if (!$finder->hasResults()) {
            throw new MissingConfigFileException();
        }

        if ($finder->count() > 1) {
            throw new AmbiguousConfigException();
        }

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $file = $iterator->current();

        return $file->getRealPath();
    }

    /**
     * If config is not explicitly set, then define some defaults from info that
     * can be derived from project structure and environment.
     */
    private function setProjectConfigDefaults()
    {
        // TODO: Create a new a ConfigDeriver class to handle this sort of
        // thing. We will also need decide / track which keys can be entered by
        // users and which keys are derived. Many of the items here would be a
        // good fit to live in a ~/.waffle.yml file.

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

    /**
     * Gets the the config item for the specified key.
     *
     * @return string|array
     */
    private function get($key)
    {
        if (isset($this->project_config[$key])) {
            return $this->project_config[$key];
        }

        return null;
    }

    /**
     * Gets the site alias as defined in the config file.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->get(self::KEY_ALIAS);
    }

    /**
     * Gets the cms as defined in the config file.
     *
     * @return string
     */
    public function getCms()
    {
        return $this->get(self::KEY_CMS);
    }

    /**
     * Gets the default upstream as defined in the config file.
     *
     * @return string
     */
    public function getDefaultUpstream()
    {
        return $this->get(self::KEY_DEFAULT_UPSTREAM);
    }

    /**
     * Gets the host value as defined in the config file.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->get(self::KEY_HOST);
    }

    /**
     * Gets the recipes as defined in the config file.
     *
     * @return string
     */
    public function getRecipes()
    {
        return $this->get(self::KEY_RECIPES);
    }

    /**
     * Gets the task as defined in the config file.
     *
     * @return string
     */
    public function getTasks()
    {
        return $this->get(self::KEY_TASKS);
    }

    /**
     * Gets the upstreams value as defined in the config file.
     *
     * @return string[]
     */
    public function getUpstreams()
    {
        $raw_upstreams =  $this->get(self::KEY_UPSTREAMS) ?? '';
        $allowed_upstreams = explode(',', $raw_upstreams);
        return $allowed_upstreams;
    }
}
