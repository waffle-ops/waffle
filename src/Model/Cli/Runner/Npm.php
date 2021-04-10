<?php

namespace Waffle\Model\Cli\Runner;

use Symfony\Component\Process\Process;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\Factory\NpmCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Npm extends BaseCliRunner
{

    /**
     * @var NpmCommandFactory
     */
    private $npmCommandFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param NpmCommandFactory $npmCommandFactory
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        NpmCommandFactory $npmCommandFactory
    ) {
        $this->npmCommandFactory = $npmCommandFactory;
        parent::__construct($context, $io);
    }

    /**
     * Install npm dependencies.
     *
     * @return Process
     */
    public function install(): Process
    {
        $command = $this->npmCommandFactory->create(['install']);
        return $command->getProcess();
    }
}
