<?php

namespace Waffle\Model\Build;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Waffle\Model\Build\Backend\ComposerBackendBuildHandler;
use Waffle\Model\Config\Item\BuildBackend;

class BackendBuildHandlerFactory implements ServiceSubscriberInterface
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
            BuildBackend::STRATEGY_NONE => NullBuildHandler::class,
            BuildBackend::STRATEGY_COMPOSER => ComposerBackendBuildHandler::class,
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

        throw new \Exception(sprintf(
            'Backend build strategy \'%s\' not implemented.',
            $strategy
        ));
    }
}
