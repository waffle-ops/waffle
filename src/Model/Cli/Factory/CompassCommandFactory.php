<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\CompassCommand;
use Waffle\Model\Context\Context;
use Exception;

class CompassCommandFactory extends BaseCliCommandFactory
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
     * @return CompassCommand
     * @throws Exception
     */
    public function create(array $args): CompassCommand
    {
        return new CompassCommand($this->context, $args);
    }
}
