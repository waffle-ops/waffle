<?php

namespace Waffle\Model\Cli;

class GitCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(array $args)
    {
        array_unshift($args, 'git');
        parent::__construct($args);
    }
}
