<?php

namespace Waffle\Model\Command;

use Waffle\Command\Task\ConfigDefinedTask;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\Factory\GenericCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class TaskFactory
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var IOStyle
     */
    private $io;

    /**
     * @var GenericCommandFactory
     */
    private $genericCommandFactory;

    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param GenericCommandFactory $genericCommandFactory
     * @param CliHelper $cliHelper
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        GenericCommandFactory $genericCommandFactory,
        CliHelper $cliHelper
    ) {
        $this->context = $context;
        $this->io = $io;
        $this->genericCommandFactory = $genericCommandFactory;
        $this->cliHelper = $cliHelper;
    }

    /**
     * Creates a new ConfigDefinedTask.
     *
     * @param string $taskKey
     *   The task key.
     *
     * @return ConfigDefinedTask
     */
    public function create(string $taskKey)
    {
        return new ConfigDefinedTask(
            $this->context,
            $this->io,
            $this->genericCommandFactory,
            $this->cliHelper,
            $taskKey
        );
    }
}
