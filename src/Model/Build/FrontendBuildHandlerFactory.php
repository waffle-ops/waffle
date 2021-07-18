<?php

namespace Waffle\Model\Build;

use Waffle\Helper\DiHelper;
use Waffle\Model\Build\Frontend\GulpFrontendBuildHandler;
use Waffle\Model\Build\Frontend\CompassFrontendBuildHandler;
use Waffle\Model\Config\Item\BuildFrontend;

class FrontendBuildHandlerFactory
{
    /**
     * @var DiHelper
     */
    private $diHelper;

    /**
     * Constructor
     *
     * @param DiHelper $diHelper
     */
    public function __construct(
        DiHelper $diHelper
    ) {
        $this->diHelper = $diHelper;
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
                return $this->diHelper->getContainer()->get(NullBuildHandler::class);

            case BuildFrontend::STRATEGY_GULP:
                return $this->diHelper->getContainer()->get(GulpFrontendBuildHandler::class);

            case BuildFrontend::STRATEGY_COMPASS:
                return $this->diHelper->getContainer()->get(CompassFrontendBuildHandler::class);

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
