<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\NpmCommand;
use Waffle\Model\Context\Context;

class NpmCommandFactory extends BaseCliCommandFactory
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
     * Creates a new ComposerCommand instance.
     *
     * @param string[] $args
     *
     * @return NpmCommand
     */
    public function create(array $args)
    {
        return new NpmCommand($this->context, $args);
    }
}
