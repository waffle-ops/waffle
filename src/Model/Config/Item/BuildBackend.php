<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class BuildBackend extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'build-backend';

    /**
     * @var string
     *
     * Key for strategy element.
     */
    public const STRATEGY_KEY = 'strategy';

    /**
     * @var string
     *
     * Expected value in cases where no backend strategy is needed.
     */
    public const STRATEGY_NONE = 'none';

    /**
     * @var string
     *
     * Expected value in cases where composer backend strategy is needed.
     */
    public const STRATEGY_COMPOSER = 'composer';

    /**
     * @var array
     *
     * An array containing a list of avaliable strategy options.
     */
    public const STRATEGY_OPTIONS = [
        self::STRATEGY_NONE,
        self::STRATEGY_COMPOSER,
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
