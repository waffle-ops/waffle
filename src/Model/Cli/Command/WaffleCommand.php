<?php

namespace Waffle\Model\Cli\Command;

use Waffle\Model\Cli\BaseCliCommand;
use Waffle\Model\Context\Context;

class WaffleCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(Context $context, array $args)
    {
        global $argv;
        $waffle_bin = realpath($argv[0]);
        array_unshift($args, $waffle_bin);
        parent::__construct($context, $args);
    }
}
