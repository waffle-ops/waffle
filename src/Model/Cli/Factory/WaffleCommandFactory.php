<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\WaffleCommand;
use Waffle\Model\Context\Context;

class WaffleCommandFactory extends BaseCliCommandFactory
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
        parent::__construct($context);
    }

    /**
     * Creates a new WaffleCommand instance.
     *
     * @param string[] $args
     *
     * @return WaffleCommand
     */
    public function create(array $args)
    {
        return new WaffleCommand($this->context, $args);
    }
}
