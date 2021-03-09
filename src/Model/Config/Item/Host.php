<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class Host extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'host';

        /**
     * @var string
     *
     * Expected value for an Acquia site.
     */
    public const OPTION_ACQUIA = 'acquia';

    /**
     * @var string
     *
     * Expected value for a Pantheon site.
     */
    public const OPTION_PANTHEON = 'pantheon';

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
        self::OPTION_ACQUIA,
        self::OPTION_PANTHEON,
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
