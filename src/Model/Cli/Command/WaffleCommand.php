<?php

namespace Waffle\Model\Cli\Command;

use Waffle\Model\Cli\Command\BaseCliCommand;

class WaffleCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(ontext $context, array $args)
    {
        global $argv;
        $waffle_bin = realpath($argv[0]);
        array_unshift($args, $waffle_bin);
        parent::__construct($context, $args);
    }
}
