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
        $config = $this->getConfig();

        if (isset($config['default_upstream'])) {
            return $config['default_upstream'];
        }

        // If we know this is a Pantheon site, let's use live.
        if ($config['host'] === 'pantheon') {
            return 'live';
        }

        // Acquia-like sites are most common, so if all else fails, go with 'prod'.
        return 'prod';
    }
}
