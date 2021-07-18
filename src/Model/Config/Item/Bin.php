<?php

namespace Waffle\Model\Config\Item;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Waffle\Model\Config\BaseConfigItem;
use Waffle\Model\Config\ConfigItemInterface;

class Bin extends BaseConfigItem
{
    /**
     * @var string
     *
     * The key for this config item.
     */
    public const KEY = 'bin';

    /**
     * @var string
     *
     * Config key for the composer binary.
     */
    public const BIN_COMPOSER = 'composer';

    /**
     * @var string
     *
     * Config key for the drush binary.
     */
    public const BIN_DRUSH = 'drush';

    /**
     * @var string
     *
     * Config key for the git binary.
     */
    public const BIN_GIT = 'git';

    /**
     * @var string
     *
     * Config key for the gulp binary.
     */
    public const BIN_GULP = 'gulp';

    /**
     * @var string
     *
     * Config key for the compass binary.
     */
    public const BIN_COMPASS = 'compass';

    /**
     * @var string
     *
     * Config key for the npm binary.
     */
    public const BIN_NPM = 'npm';

    /**
     * @var string
     *
     * Config key for the symfony binary.
     */
    public const BIN_SYMFONY = 'symfony';

    /**
     * @var string
     *
     * Config key for the wp-cli binary.
     */
    public const BIN_WP_CLI = 'wp';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::KEY,
            [
                ConfigItemInterface::SCOPE_GLOBAL,
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
        return $nodeBuilder->arrayNode(self::KEY)
            ->children()
                ->scalarNode(self::BIN_COMPOSER)->end()
                ->scalarNode(self::BIN_DRUSH)->end()
                ->scalarNode(self::BIN_GIT)->end()
                ->scalarNode(self::BIN_GULP)->end()
                ->scalarNode(self::BIN_NPM)->end()
                ->scalarNode(self::BIN_SYMFONY)->end()
                ->scalarNode(self::BIN_WP_CLI)->end()
            ->end();
    }
}
