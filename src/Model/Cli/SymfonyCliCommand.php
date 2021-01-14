<?php

namespace Waffle\Model\Cli;

use Exception;

class SymfonyCliCommand extends BaseCliCommand
{
    
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $args)
    {
        array_unshift($args, 'symfony');
        parent::__construct($args);
    }
}
