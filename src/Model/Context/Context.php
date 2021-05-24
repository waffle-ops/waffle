<?php

namespace Waffle\Model\Context;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Waffle\Model\Cli\Runner\Composer;
use Waffle\Model\Config\ConfigTreeService;
use Waffle\Model\Config\Item\Alias;
use Waffle\Model\Config\Item\Bin;
use Waffle\Model\Config\Item\Cms;
use Waffle\Model\Config\Item\CommandPrefix;
use Waffle\Model\Config\Item\ComposerPath;
use Waffle\Model\Config\Item\DefaultUpstream;
use Waffle\Model\Config\Item\EnvironmentVariables;
use Waffle\Model\Config\Item\Host;
use Waffle\Model\Config\Item\LocalSettingsFilename;
use Waffle\Model\Config\Item\Recipes;
use Waffle\Model\Config\Item\Tasks;
use Waffle\Model\Config\Item\Timeout;
use Waffle\Model\Config\Item\Upstreams;

class Context implements ConfigurationInterface
{
    /**
     * This is a wrapper class for the various types of contexts of which
     * Waffle will run. As it stands currently, Waffle will keep track of
     * multiple layers of contexts.
     *
     * The Contexts will be loaded in the following order:
     *
     * GlobalContext  - This allows global configuration of Waffle.
     * ProjectContext - This is project specific configuration of Waffle.
     * LocalContext   - This is for overriding project specific configuration.
     *
     * Consider a DefaultContext that is loaded in cases where no value is provided.
     * For example, if the host is Pantheon, we can assume live as a default upstream
     * if none is provided.
     */

    /**
     * @var ConfigTreeService
     */
    protected $configTreeService;

    /**
     * The config from the global context.
     *
     * @var array
     */
    protected $globalConfig;

    /**
     * The config from the project context.
     *
     * @var array
     */
    protected $projectConfig;

    /**
     * The config from the local context.
     *
     * @var array
     */
    protected $localConfig;

    /**
     * The combined config from all available contexts.
     *
     * @var array
     */
    protected $config;

    /**
     * The directory in which the current task needs to run.
     *
     * @var string
     */
    protected $taskWorkingDirectory;

    /**
     * Constructor
     *
     * @param ConfigTreeService $configTreeService
     * @param GlobalContext $globalContext
     * @param ProjectContext $projectContext
     * @param LocalContext $localContext
     */
    public function __construct(
        ConfigTreeService $configTreeService,
        GlobalContext $globalContext,
        ProjectContext $projectContext,
        LocalContext $localContext
    ) {
        $this->configTreeService = $configTreeService;

        $this->globalConfig = $globalContext->getConfig();
        $this->projectConfig = $projectContext->getConfig();
        $this->localConfig = $localContext->getConfig();

        $processor = new Processor();

        $configs = [
            $this->globalConfig,
            $this->projectConfig,
            $this->localConfig,
        ];

        $this->config = $processor->processConfiguration(
            $this,
            $configs
        );

        $this->resetTaskWorkingDirectory();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        return $this->configTreeService->getApplicationConfigDefinition();
    }

    /**
     * Checks if project config exists.
     *
     * @return bool
     */
    public function hasProjectConfig()
    {
        return !empty($this->projectConfig);
    }

    /**
     * Checks if project config exists.
     *
     * @return bool
     */
    public function hasLocalConfig()
    {
        return !empty($this->localConfig);
    }

    /**
     * Gets the the config item for the specified key.
     *
     * @return string|array
     */
    public function get($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
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
        return $this->get(Alias::KEY);
    }

    /**
     * Gets the cms as defined in config.
     *
     * @return string
     */
    public function getCms()
    {
        return $this->get(Cms::KEY);
    }

    /**
     * Gets the default upstream as defined in config.
     *
     * @return string
     */
    public function getDefaultUpstream()
    {
        return $this->get(DefaultUpstream::KEY);
    }

    /**
     * Gets the host value as defined in config.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->get(Host::KEY);
    }

    /**
     * Gets the recipes as defined in config.
     *
     * @return string
     */
    public function getRecipes()
    {
        return $this->get(Recipes::KEY);
    }

    /**
     * Gets the task as defined in config.
     *
     * @return string
     */
    public function getTasks()
    {
        return $this->get(Tasks::KEY);
    }

    /**
     * Gets the upstreams value as defined in the config file.
     *
     * @todo See src/Model/Config/Item/Upstreams.php
     *
     * @return string[]
     */
    public function getUpstreams()
    {
        /**
         * @todo We should convert this to an array node so that the Symfony
         * config processor just hands us an array.
         */

        $raw_upstreams =  $this->get(Upstreams::KEY) ?? '';
        $allowed_upstreams = explode(',', $raw_upstreams);
        return $allowed_upstreams;
    }

    /**
     * Gets the composer path as defined in the config file.
     *
     * @todo See src/Model/Config/Item/ComposerPath.php
     *
     * @return string
     */
    public function getComposerPath()
    {
        /**
         * @todo The code below is part of the previous implementation of this,
         * that needs to be re-implemented.
         *
         * See src/Model/Config/Item/ComposerPath.php
         */

        $path = $this->get(ComposerPath::KEY);

        if (empty($path)) {
            $path = Composer::determineComposerPath();
        }

        return $path;
    }

    /**
     * Gets the command prefix.
     *
     * @todo See src/Model/Config/Item/CommandPrefix.php
     *
     * @return array|string|null
     */
    public function getCommandPrefix()
    {
        // See src/Model/Config/Item/CommandPrefix.php
        $prefix = $this->get(CommandPrefix::KEY);

        if (!is_null($prefix)) {
            trigger_deprecation(
                'waffle-ops/waffle',
                'v0.1.0',
                sprintf(
                    'The "%s" configuration item is deprecated and will be removed'
                    . ' before v1.0.0. Use the "bin" configuration item instead.',
                    CommandPrefix::KEY
                )
            );
        }

        return $prefix;
    }

    /**
     * Gets the local settings filename.
     *
     * @todo See src/Model/Config/Item/LocalSettingFilename.php
     *
     * Default value: settings.local.php
     *
     * @return array|string|null
     */
    public function getLocalSettingsFilename()
    {
        /**
         * @todo The code below is part of the previous implementation of this,
         * that needs to be re-implemented.
         *
         * See src/Model/Config/Item/LocalSettingFilename.php
         */

        $file = $this->get(LocalSettingsFilename::KEY);

        if (empty($file)) {
            $time = 'settings.local.php';
        }

        return $file;
    }

    /**
     * Gets the timeout time (in seconds) for commands.
     *
     * @todo See src/Model/Config/Item/Timeout.php
     *
     * @return array|string|null
     */
    public function getTimeout()
    {
        /**
         * @todo The code below is part of the previous implementation of this,
         * that needs to be re-implemented.
         *
         * See src/Model/Config/Item/Timeout.php
         */

        $time = $this->get(Timeout::KEY);

        if (empty($time)) {
            $time = 300;
        }

        return $time;
    }

    /**
     * @return string
     */
    public function getTaskWorkingDirectory()
    {
        return $this->taskWorkingDirectory;
    }

    /**
     * @param string $directory
     * @throws \Exception
     */
    public function setTaskWorkingDirectory(string $directory)
    {
        $path = realpath($directory);

        if ($path === false || !is_dir($path)) {
            throw new \Exception(sprintf('Directory with path %s does not exist.', $directory));
        }

        $this->taskWorkingDirectory = $path;
    }

    /**
     * Resets the task working directory. This is needed for recipes that call
     * multiple tasks but may need different task directories.
     */
    public function resetTaskWorkingDirectory()
    {
        $this->taskWorkingDirectory = null;
    }

    /**
     * Gets environment variables as defined in config.
     *
     * @return array
     */
    public function getEnvironmentVariables()
    {
        return $this->get(EnvironmentVariables::KEY);
    }

    /**
     * Gets the configured binary for use with external tools.
     *
     * Returns $bin in the event that no override is found.
     *
     * @return string
     */
    public function getBin($bin)
    {
        return $this->config[Bin::KEY][$bin] ?? $bin;
    }
}
