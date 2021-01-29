<?php

namespace Waffle\Model\Cli;

use Exception;

class WpCliCommand extends BaseCliCommand
{
    
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $args)
    {
        array_unshift($args, 'wp');
        parent::__construct($args);
    }
}
