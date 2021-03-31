<?php

namespace Waffle\Model\Cli\Runner;

use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\Factory\GulpCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Gulp extends BaseCliRunner
{

    /**
     * @var GulpCommandFactory
     */
    private $gulpCommandFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param GulpCommandFactory $gulpCommandFactory
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        GulpCommandFactory $gulpCommandFactory
    ) {
        $this->gulpCommandFactory = $gulpCommandFactory;
        parent::__construct($context, $io);
    }

    /**
     * Runs 'gulp build'.
     *
     * Assumption is that the 'build' task exists in the gulp config file.
     *
     * @return Process
     */
    public function build(): Process
    {
        $command = $this->gulpCommandFactory->create(['build']);
        return $command->getProcess();
    }
}
