<?php

namespace Waffle\Model\Context;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Waffle\Model\Config\ConfigTreeService;

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

        $processor = new Processor();

        $configs = [
            $globalContext->getConfig(),
            $projectContext->getConfig(),
            $localContext->getConfig(),
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
}
