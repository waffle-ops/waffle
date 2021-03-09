<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\DrushCommand;
use Waffle\Model\Context\Context;

class DrushCommandFactory extends BaseCliCommandFactory
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Creates a new DrushCommand instance.
     *
     * @param string[] $args
     *
     * @return DrushCommand
     */
    public function create(array $args)
    {
        return new DrushCommand($this->context, $args);
    }
}
