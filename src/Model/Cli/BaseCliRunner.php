<?php

namespace Waffle\Model\Cli;

use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

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
     * @param IOStyle $io
     */
    public function __construct(
        Context $context,
        IOStyle $io
    ) {
        $this->context = $context;
        $this->io = $io;
    }
}
