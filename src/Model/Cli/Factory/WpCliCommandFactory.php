<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\WpCliCommand;
use Waffle\Model\Context\Context;

class WpCliCommandFactory extends BaseCliCommandFactory
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
     * Creates a new WpCliCommand instance.
     *
     * @param string[] $args
     *
     * @return WpCliCommand
     */
    public function create(array $args)
    {
        return new WpCliCommand($this->context, $args);
    }
}
