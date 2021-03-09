<?php

namespace Waffle\Model\Cli\Command;

use Waffle\Model\Cli\BaseCliCommand;
use Waffle\Model\Context\Context;

class ComposerCommand extends BaseCliCommand
{

    /**
     * {@inheritdoc}
     */
    public function __construct(Context $context, array $args)
    {
        array_unshift($args, 'composer');
        parent::__construct($context, $args);
    }
}
