<?php

namespace Waffle\Model\Build;

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Waffle\Model\Build\Backend\ComposerBackendBuildHandler;
use Waffle\Model\Config\Item\BuildBackend;

class BackendBuildHandlerFactory implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

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
                return $this->none();

            case BuildBackend::STRATEGY_COMPOSER:
                return $this->composer();

            default:
                throw new \Exception(sprintf(
                    'Backend build strategy \'%s\' not implemented.',
                    $strategy
                ));
        }
    }

    /**
     * Gets the 'none' backend build handler.
     *
     * The method name is intentional.
     * See https://symfony.com/doc/current/service_container/service_subscribers_locators.html#service-subscriber-trait
     *
     * @return NullBuildHandler
     */
    private function none(): NullBuildHandler
    {
        return $this->container->get(__METHOD__);
    }

    /**
     * Gets the 'composer' backend build handler.
     *
     * The method name is intentional.
     * See https://symfony.com/doc/current/service_container/service_subscribers_locators.html#service-subscriber-trait
     *
     * @return ComposerBackendBuildHandler
     */
    private function composer(): ComposerBackendBuildHandler
    {
        return $this->container->get(__METHOD__);
    }
}
