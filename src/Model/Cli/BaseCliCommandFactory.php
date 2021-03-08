<?php

namespace Waffle\Model\Cli;

use Waffle\Model\Context\Context;

abstract class BaseCliCommandFactory
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
        $this->context = $context;
    }

    /**
     * Creates a new ComposerCommand instance.
     *
     * @param string[] $args
     *
     * @return BaseCliCommand
     */
    abstract public function create(array $args);
}
