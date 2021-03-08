<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class Cms extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'cms';

    /**
     * @var string
     *
     * Expected value for a Drupal 7 site.
     */
    public const OPTION_DRUPAL_7 = 'drupal7';

    /**
     * @var string
     *
     * Expected value for a Drupal 8 site.
     */
    public const OPTION_DRUPAL_8 = 'drupal8';

    /**
     * @var string
     *
     * Expected value for a WordPress site.
     */
    public const OPTION_WORDPRESS = 'wordpress';

    /**
     * @var string
     *
     * Catch-all key for cases where users want to use Waffle outside of
     * officially supported project types.
     */
    public const OPTION_OTHER = 'other';

    /**
     * @var array
     *
     * An aray containing all possible options for this config item.
     */
    public const OPTIONS = [
        self::OPTION_DRUPAL_7,
        self::OPTION_DRUPAL_8,
        self::OPTION_WORDPRESS,
        self::OPTION_OTHER,
    ];

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
