<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\GenericCommand;
use Waffle\Model\Context\Context;

class GenericCommandFactory extends BaseCliCommandFactory
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
     * Creates a new GenericCommand instance.
     *
     * @param string[] $args
     *
     * @return GenericCommand
     */
    public function create(array $args)
    {
        return new GenericCommand($this->context, $args);
    }
}
