<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\ComposerCommand;
use Waffle\Model\Context\Context;

class ComposerCommandFactory extends BaseCliCommandFactory
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
     * @return ComposerCommand
     */
    public function create(array $args)
    {
        return new ComposerCommand($this->context, $args);
    }
}
