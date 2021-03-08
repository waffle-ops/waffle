<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Model\Config\Item\Cms;

class SiteSyncFactory
{

    /**
     * Gets the site sync adapter.
     *
     * @return SiteSyncInterface
     */
    public function getSiteSyncAdapter($cms)
    {
        switch ($cms) {
            case Cms::OPTION_DRUPAL_7:
            case Cms::OPTION_DRUPAL_8:
                return new DrushSiteSync();

            default:
                throw new \Exception(sprintf('Site sync adapter for %s not supported.', $cms));
        }
    }
}
