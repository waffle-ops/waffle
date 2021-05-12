<?php

namespace Waffle\Helper;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DiHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * NOTE: This class should be used sparingly. It's primary goal is to skirt
     * around issues around loaing dependencies that are not needed. For
     * example, we don't need to load the WpCli runner class if we are working
     * with a Drupal site. Due to the auto-wired services, any dependency that
     * is used in a constructor is initialized, which we want to avoid in some
     * cases.
     */

    /**
     * Gets the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
