<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class CommandPrefix extends BaseConfigItem
{
    /**
     * @todo Consider removing command_prefix as a config option. For now, this
     * is included for backwards compatabilty.
     *
     * This currently serves to blindly add a prefix to CLI commands. Some
     * projects may use lando and may need to prefix 'lando' to some commands.
     * Chances are we will need to accept a xxx_prefix command key for things
     * like npm, drush, composer, etc...
     *
     * Leaving this for now until we figure out a better way of calling those
     * commands. I think a factory pattern will work well. The factory could
     * build the command and attach the prefix and add anything else that
     * may be needed.
     */

    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'command_prefix';

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
