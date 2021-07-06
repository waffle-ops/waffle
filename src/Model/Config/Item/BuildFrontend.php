<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class BuildFrontend extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'build-frontend';

    /**
     * @var string
     *
     * Key for strategy element.
     */
    public const STRATEGY_KEY = 'strategy';

    /**
     * @var string
     *
     * Expected value in cases where no frontend strategy is needed.
     */
    public const STRATEGY_NONE = 'none';

    /**
     * @var string
     *
     * Expected value in cases where gulp frontend strategy is needed.
     */
    public const STRATEGY_GULP = 'gulp';

    /**
     * @var string
     *
     * Expected value in cases where compass frontend strategy is needed.
     */
    public const STRATEGY_COMPASS = 'compass';

    /**
     * @var string
     *
     * Key for directory option.
     */
    public const DIRECTORY_KEY = 'dir';

    /**
     * @var array
     *
     * An array containing a list of available strategy options.
     */
    public const STRATEGY_OPTIONS = [
        self::STRATEGY_NONE,
        self::STRATEGY_GULP,
        self::STRATEGY_COMPASS
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
        return $nodeBuilder
            ->arrayNode(self::KEY)
                ->children()
                    ->enumNode(self::STRATEGY_KEY)
                        ->values(self::STRATEGY_OPTIONS)
                    ->end()
                    ->scalarNode(self::DIRECTORY_KEY)->end()
                ->end();
    }
}
