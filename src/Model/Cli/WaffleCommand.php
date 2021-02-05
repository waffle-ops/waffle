<?php

namespace Waffle\Model\Cli;

use Exception;

class WaffleCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $args)
    {
        global $argv;
        $waffle_bin = realpath($argv[0]);
        array_unshift($args, $waffle_bin);
        parent::__construct($args);
    }
}
