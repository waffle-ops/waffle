<?php

namespace Waffle\Model\Cli;

class DrushCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(array $args)
    {
        array_unshift($args, 'drush');
        parent::__construct($args);
    }
}
