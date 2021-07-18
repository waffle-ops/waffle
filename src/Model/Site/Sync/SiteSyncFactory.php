<?php

namespace Waffle\Model\Site\Sync;

use Waffle\Helper\DiHelper;
use Waffle\Model\Config\Item\Cms;

class SiteSyncFactory
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
                return $this->diHelper->getContainer()->get(DrushSiteSync::class);

            default:
                throw new \Exception(sprintf('Site sync adapter for %s not supported.', $cms));
        }
    }
}
