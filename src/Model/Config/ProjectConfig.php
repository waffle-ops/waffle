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
    public const KEY_COMPOSER_PATH = 'composer_path';
    public const KEY_COMMAND_PREFIX = 'command_prefix';
    public const KEY_LOCAL_SETTINGS_FILENAME = 'local_settings_filename';
    public const KEY_TIMEOUT = 'timeout';

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
     * Constants for the 'cms' config key.
     */
    public const CMS_DRUPAL_7 = 'drupal7';
    public const CMS_DRUPAL_8 = 'drupal8';
    public const CMS_WORDPRESS = 'wordpress';

    public const CMS_OPTIONS = [
        self::CMS_DRUPAL_7,
        self::CMS_DRUPAL_8,
        self::CMS_WORDPRESS,
    ];

    /**
     * Constants for the 'host' config key.
     */
    public const HOST_ACQUIA = 'acquia';
    public const HOST_PANTHEON = 'pantheon';

    public const HOST_OPTIONS = [
        self::HOST_ACQUIA,
        self::HOST_PANTHEON,
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
     * Gets the composer.json path.
     *
     * @return string
     */
    private function determineComposerPath()
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
     * Checks if a config key/value is set at all.
     *
     * Used to determine if a default value should be set when lazy loading.
     *
     * @param $key
     *
     * @return bool
     */
    private function keyExists($key)
    {
        return isset($this->project_config[$key]);
    }

    /**
     * Sets a project config key to a value.
     *
     * @param $key
     * @param $value
     */
    private function set($key, $value)
    {
        $this->project_config[$key] = $value;
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
     * Gets the composer path as defined in the config file.
     *
     * @return string
     */
    public function getComposerPath()
    {
        if (!$this->keyExists(self::KEY_COMPOSER_PATH)) {
            // Attempt to derive the composer.json path.
            $this->set(self::KEY_COMPOSER_PATH, $this->determineComposerPath());
        }

        return $this->get(self::KEY_COMPOSER_PATH);
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

    /**
     * Gets the command prefix.
     *
     * @return array|string|null
     */
    public function getCommandPrefix()
    {
        return $this->get(self::KEY_COMMAND_PREFIX);
    }

    /**
     * Gets the local settings filename.
     *
     * Default value: settings.local.php
     *
     * @return array|string|null
     */
    public function getLocalSettingsFilename()
    {
        if (!$this->keyExists(self::KEY_LOCAL_SETTINGS_FILENAME)) {
            $this->set(self::KEY_LOCAL_SETTINGS_FILENAME, 'settings.local.php');
        }

        return $this->get(self::KEY_LOCAL_SETTINGS_FILENAME);
    }

    /**
     * Gets the timeout time (in milliseconds) for commands.
     *
     * Default value: 300
     *
     * @return array|string|null
     */
    public function getTimeout()
    {
        if (!$this->keyExists(self::KEY_TIMEOUT)) {
            $this->set(self::KEY_TIMEOUT, 300);
        }

        return $this->get(self::KEY_TIMEOUT);
    }
}
