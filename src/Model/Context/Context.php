<?php

namespace Waffle\Model\Context;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Waffle\Model\Config\ConfigTreeService;
use Waffle\Model\Config\Item\Alias;
use Waffle\Model\Config\Item\Cms;
use Waffle\Model\Config\Item\CommandPrefix;
use Waffle\Model\Config\Item\ComposerPath;
use Waffle\Model\Config\Item\DefaultUpstream;
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
     * The combined config from all avaliable contexts.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ConfigTreeService
     * @param GlobalContext
     * @param ProjectContext
     * @param LocalContext
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
    private function get($key)
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
         */

        // if (!$this->keyExists(self::KEY_COMPOSER_PATH)) {
        //     // Attempt to derive the composer.json path.
        //     $this->set(self::KEY_COMPOSER_PATH, $this->determineComposerPath());
        // }

        return $this->get(ComposerPath::KEY);
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
        return $this->get(CommandPrefix::KEY);
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
         */

        // if (!$this->keyExists(self::KEY_LOCAL_SETTINGS_FILENAME)) {
        //     $this->set(self::KEY_LOCAL_SETTINGS_FILENAME, 'settings.local.php');
        // }

        return $this->get(LocalSettingsFilename::KEY);
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
         */

        // if (!$this->keyExists(self::KEY_TIMEOUT)) {
        //     $this->set(self::KEY_TIMEOUT, 300);
        // }

        return $this->get(Timeout::KEY);
    }
}
