<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\SymfonyCommand;
use Waffle\Model\Context\Context;

class SymfonyCommandFactory extends BaseCliCommandFactory
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
     * Creates a new SymfonyCommand instance.
     *
     * @param string[] $args
     *
     * @return SymfonyCommand
     */
    public function create(array $args)
    {
        return new SymfonyCommand($this->context, $args);
    }
}
