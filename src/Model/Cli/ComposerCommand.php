<?php

namespace Waffle\Model\Cli;

class ComposerCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(array $args)
    {
        array_unshift($args, 'composer');
        parent::__construct($args);
    }
}
