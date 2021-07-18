<?php

namespace Waffle\Model\Cli\Runner;

use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\Factory\CompassCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Compass extends BaseCliRunner
{

    /**
     * @var CompassCommandFactory
     */
    private $compassCommandFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param CompassCommandFactory $compassCommandFactory
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        CompassCommandFactory $compassCommandFactory
    ) {
        $this->compassCommandFactory = $compassCommandFactory;
        parent::__construct($context, $io);
    }

    /**
     * Runs 'compass compile --force'.
     *
     * Assumption is that the 'build' task exists in the compass config file.
     *
     * @return Process
     */
    public function compile(): Process
    {
        $command = $this->compassCommandFactory->create(['compile', '--force']);
        return $command->getProcess();
    }
}
