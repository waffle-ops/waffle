<?php

namespace Waffle\Model\Context;

class Context
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
     * @var GlobalContext
     */
    protected $globalContext;

    /**
     * @var ProjectContext
     */
    protected $projectContext;

    /**
     * @var LocalContext
     */
    protected $localContext;

    /**
     * The combined config from all avaliable contexts.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param GlobalContext
     * @param ProjectContext
     * @param LocalContext
     */
    public function __construct(
        GlobalContext $globalContext,
        ProjectContext $projectContext,
        LocalContext $localContext
    ) {
        $this->globalContext = $globalContext;
        $this->projectContext = $projectContext;
        $this->localContext = $localContext;

        $this->config = $this->buildConfig($globalContext, $projectContext, $localContext);
    }

    /**
     * Helper method to build the combined config array from all avaliable
     * contexts.
     *
     * @param GlobalContext
     * @param ProjectContext
     * @param LocalContext
     */
    private function buildConfig(
        GlobalContext $globalContext,
        ProjectContext $projectContext,
        LocalContext $localContext
    ) {
        // This works, but only for top level keys.
        // Tasks, Recipes and anything else that is nested in the config file
        // will need some extra love to make this work correctly.
        // For now, I think this is good enough.

        // Need to think more about what a globally defined task looks like.
        // If a task that calls a local shell script:
        //   Is it relative to the project?
        //   Is it relative to the project global config path?

        // Other options:
        //   Perhaps we add a normalize() to the stored configs. The global normalize
        //   could unset tasks and recipes for example.
        //   Perhaps a validate() in the stored configs. Maybe not all config keys can
        //   be overidden.

        return array_merge(
            $this->globalContext->getConfig(),
            $this->projectContext->getConfig(),
            $this->localContext->getConfig(),
        );
    }
}
