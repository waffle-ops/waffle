<?php

namespace Waffle\Model\Build;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Waffle\Model\Build\Frontend\GulpFrontendBuildHandler;
use Waffle\Model\Build\Frontend\CompassFrontendBuildHandler;
use Waffle\Model\Config\Item\BuildFrontend;

class FrontendBuildHandlerFactory implements ServiceSubscriberInterface
{
    /**
     * Service locator.
     *
     * @var ContainerInterface
     */
    private $locator;

    /**
     * Constructor for BackendBuildHandlerFactory
     *
     * @param ContainerInterface $locator
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            BuildFrontend::STRATEGY_NONE => NullBuildHandler::class,
            BuildFrontend::STRATEGY_GULP => GulpFrontendBuildHandler::class,
            BuildFrontend::STRATEGY_COMPASS => CompassFrontendBuildHandler::class,
        ];
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
        if ($this->locator->has($strategy)) {
            return $this->locator->get($strategy);
        }

        throw new \Exception(
            sprintf(
                'Frontend build strategy \'%s\' not implemented.',
                $strategy
            )
        );
    }
}
