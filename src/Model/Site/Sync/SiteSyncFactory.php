<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Model\Config\Item\Cms;

class SiteSyncFactory
{

    /**
     * @var DrushSiteSync
     */
    private $drushSiteSync;

    /**
     * Constructor
     *
     * @param DrushSiteSync $drushSiteSync
     */
    public function __construct(
        DrushSiteSync $drushSiteSync
    ) {
        $this->drushSiteSync = $drushSiteSync;
    }

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
            case Cms::OPTION_DRUPAL_9:
                return $this->drushSiteSync;

            default:
                throw new \Exception(sprintf('Site sync adapter for %s not supported.', $cms));
        }
    }
}
