<?php

namespace Waffle\Model\Cli;

use Waffle\Model\Context\Context;
use Waffle\Model\IO\IO;

class BaseCliRunner
{
    /**
     * @var IO
     */
    protected $io;

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
        $this->io = IO::getInstance()->getIO();
        $this->context = $context;
    }
}
