<?php

namespace Waffle\Model\Build;

use Waffle\Model\Build\Frontend\GulpFrontendBuildHandler;
use Waffle\Model\Build\Frontend\CompassFrontendBuildHandler;
use Waffle\Model\Config\Item\BuildFrontend;

class FrontendBuildHandlerFactory
{
    /**
     * @var GulpFrontendBuildHandler
     */
    private $gulpFrontendBuildHandler;

    /**
     * @var CompassFrontendBuildHandler
     */
    private $compassFrontendBuildHandler;

    /**
     * Constructor
     *
     * @param GulpFrontendBuildHandler $gulpFrontendBuildHandler
     * @param CompassFrontendBuildHandler $compassFrontendBuildHandler
     */
    public function __construct(
        GulpFrontendBuildHandler $gulpFrontendBuildHandler,
        CompassFrontendBuildHandler $compassFrontendBuildHandler
    ) {
        $this->gulpFrontendBuildHandler = $gulpFrontendBuildHandler;
        $this->compassFrontendBuildHandler = $compassFrontendBuildHandler;
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
            case BuildFrontend::STRATEGY_NONE:
                return new NullBuildHandler();

            case BuildFrontend::STRATEGY_GULP:
                return $this->gulpFrontendBuildHandler;

            case BuildFrontend::STRATEGY_COMPASS:
                return $this->compassFrontendBuildHandler;

            default:
                throw new \Exception(
                    sprintf(
                        'Frontend build strategy \'%s\' not implemented.',
                        $strategy
                    )
                );
        }
    }
}
