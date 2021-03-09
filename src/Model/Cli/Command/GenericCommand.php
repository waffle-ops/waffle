<?php

namespace Waffle\Model\Cli\Command;

use Waffle\Model\Cli\BaseCliCommand;
use Waffle\Model\Context\Context;

class GenericCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(Context $context, array $args)
    {
        parent::__construct($context, $args);
    }
}
