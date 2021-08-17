<?php

namespace Waffle\Model\Build;

use Waffle\Model\Build\Backend\ComposerBackendBuildHandler;
use Waffle\Model\Config\Item\BuildBackend;

class BackendBuildHandlerFactory
{

    /**
     * @var ComposerBackendBuildHandler
     */
    private $composerHandler;

    /**
     * Constructor
     *
     * @param ComposerBackendBuildHandler $composerHandler
     */
    public function __construct(
        ComposerBackendBuildHandler $composerHandler
    ) {
        $this->composerHandler = $composerHandler;
    }

    /**
     * Gets a instance of a backend builder.
     *
     * @param string $strategy
     *
     * @return BuildHandlerInterface
     */
    public function getHandler(string $strategy)
    {
        switch ($strategy) {
            case BuildBackend::STRATEGY_NONE:
                return new NullBuildHandler();

            case BuildBackend::STRATEGY_COMPOSER:
                return $this->composerHandler;

            default:
                throw new \Exception(sprintf(
                    'Backend build strategy \'%s\' not implemented.',
                    $strategy
                ));
        }
    }
}
