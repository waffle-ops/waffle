<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class Tasks extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'tasks';

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
                ->normalizeKeys(false)
                ->scalarPrototype()->end();
    }
}
