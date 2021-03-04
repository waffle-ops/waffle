<?php

namespace Waffle\Model\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigTreeService
{

    /**
     * @var ConfigItemInterface[]
     *
     * List of all global config items.
     */
    protected $globalConfigItems = [];

    /**
     * @var ConfigItemInterface[]
     *
     * List of all project config items.
     */
    protected $projectConfigItems = [];

    /**
     * @var ConfigItemInterface[]
     *
     * List of all local config items.
     */
    protected $localConfigItems = [];

    /**
     * @var ConfigItemInterface[]
     *
     * List of all config items.
     */
    protected $applicationConfigItems = [];

    /**
     * Constructor
     *
     * @param iterable
     *   All avaliable config items.
     */
    public function __construct(iterable $configKeys)
    {
        foreach ($configKeys as $configKey) {
            if ($configKey->isGlobalScope()) {
                $this->globalConfigItems[] = $configKey;
            }

            if ($configKey->isProjectScope()) {
                $this->projectConfigItems[] = $configKey;
            }

            if ($configKey->isLocalScope()) {
                $this->localConfigItems[] = $configKey;
            }
        }

        $this->applicationConfigItems = $configKeys;
    }

    /**
     *
     */
    public function getGlobalConfigDefinition()
    {
        $treeBuilder = new TreeBuilder(ConfigItemInterface::SCOPE_GLOBAL);

        $children = $treeBuilder->getRootNode()->children();

        foreach ($this->globalConfigItems as $item) {
            $children->append($item->getDefinition());
        }

        $children->end();

        return $treeBuilder;
    }

    /**
     *
     */
    public function getProjectConfigDefinition()
    {
        $treeBuilder = new TreeBuilder(ConfigItemInterface::SCOPE_PROJECT);

        $children = $treeBuilder->getRootNode()->children();

        foreach ($this->projectConfigItems as $item) {
            $children->append($item->getDefinition());
        }

        $children->end();

        return $treeBuilder;
    }

    /**
     *
     */
    public function getLocalConfigDefinition()
    {
        $treeBuilder = new TreeBuilder(ConfigItemInterface::SCOPE_LOCAL);

        $children = $treeBuilder->getRootNode()->children();

        foreach ($this->localConfigItems as $item) {
            $children->append($item->getDefinition());
        }

        $children->end();

        return $treeBuilder;
    }

    /**
     *
     */
    public function getApplicationConfigDefinition()
    {
        $treeBuilder = new TreeBuilder('application');

        $children = $treeBuilder->getRootNode()->children();

        foreach ($this->applicationConfigItems as $item) {
            $children->append($item->getDefinition());
        }

        $children->end();

        return $treeBuilder;
    }
}
