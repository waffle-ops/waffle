<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class LocalSettingsFilename extends BaseConfigItem
{
    /**
     * @todo Consider removing local_settings_filename as a config option. For
     * now, this is included for backwards compatabilty.
     *
     * Similar to the drush runner class, I think the wp-cli runner class
     * would be a good candidate to absorb this feature for Wordpress. We can
     * be opinionated an make sure that the drupal and wordpress projects use
     * consistent local settings file.
     *
     * Right now, this is only used in the context of drupal sites. If it stays
     * that way, I think we could remove this and instead emit an error if the
     * local settings file we expect is not found. A similar pattern can be
     * followed for wordpress.
     */

    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'local_settings_filename';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::KEY,
            [
                ConfigItemInterface::SCOPE_PROJECT,
                ConfigItemInterface::SCOPE_LOCAL,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $nodeBuilder = new NodeBuilder();
        return $nodeBuilder->scalarNode(self::KEY);
    }
}
