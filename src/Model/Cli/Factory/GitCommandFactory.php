<?php

namespace Waffle\Model\Cli\Factory;

use Waffle\Model\Cli\BaseCliCommandFactory;
use Waffle\Model\Cli\Command\GitCommand;
use Waffle\Model\Context\Context;

class GitCommandFactory extends BaseCliCommandFactory
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
     * Creates a new GitCommand instance.
     *
     * @param string[] $args
     *
     * @return GitCommand
     */
    public function create(array $args)
    {
        return new GitCommand($this->context, $args);
    }
}
