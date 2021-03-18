<?php

namespace Waffle\Model\Build;

use Waffle\Helper\DiHelper;
use Waffle\Model\Build\Backend\ComposerBackendBuildHandler;
use Waffle\Model\Config\Item\BuildBackend;

class BackendBuildHandlerFactory
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
            case BuildBackend::STRATEGY_NONE:
                return $this->diHelper->getContainer()->get(NullBuildHandler::class);

            case BuildBackend::STRATEGY_COMPOSER:
                return $this->diHelper->getContainer()->get(ComposerBackendBuildHandler::class);

            default:
                throw new \Exception(sprintf(
                    'Backend build strategy \'%s\' not implemented.',
                    $strategy
                ));
        }
    }
}
