<?php

namespace Waffle\Traits;

trait DefaultUpstreamTrait
{

    /**
     * getDefaultUpstream
     *
     * Gets the default upstream option.
     *
     * @return string
     */
    private function getDefaultUpstream()
    {
        $config =  $this->getConfig();
        $default_upstream = $config->getDefaultUpstream();

        if (!empty($default_upstream)) {
            return $default_upstream;
        }

        // If we know this is a Pantheon site, let's use live.
        $host = $config->getHost();
        if (!empty($host) && ($host === 'pantheon')) {
            return 'live';
        }

        // Acquia-like sites are most common, so if all else fails, go with 'prod'.
        return 'prod';
    }
}
