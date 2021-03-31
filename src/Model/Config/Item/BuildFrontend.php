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
     * @var array
     *
     * An array containing a list of avaliable strategy options.
     */
    public const STRATEGY_OPTIONS = [
        self::STRATEGY_NONE,
        self::STRATEGY_GULP,
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
                ->end();
    }
}
