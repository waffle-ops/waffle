<?php

namespace Waffle\Model\Context;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Waffle\Model\Config\ConfigTreeService;
use Waffle\Model\Config\Item\Alias;
use Waffle\Model\Config\Item\Cms;
use Waffle\Model\Config\Item\DefaultUpstream;
use Waffle\Model\Config\Item\Host;
use Waffle\Model\Config\Item\Recipes;
use Waffle\Model\Config\Item\Tasks;
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
}
