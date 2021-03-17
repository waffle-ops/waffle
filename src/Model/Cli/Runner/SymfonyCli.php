<?php

namespace Waffle\Model\Cli\Runner;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\Factory\GenericCommandFactory;
use Waffle\Model\Cli\Factory\SymfonyCliCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class SymfonyCli extends BaseCliRunner
{
    /**
     * @var GenericCommandFactory
     */
    private $genericCommandFactory;

    /**
     * @var SymfonyCliCommandFactory
     */
    private $symfonyCliCommandFactory;

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
     * @param SymfonyCliCommandFactory $symfonyCliCommandFactory
     * @param CliHelper $cliHelper
     *
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        GenericCommandFactory $genericCommandFactory,
        SymfonyCliCommandFactory $symfonyCliCommandFactory,
        CliHelper $cliHelper
    ) {
        $this->genericCommandFactory = $genericCommandFactory;
        $this->symfonyCliCommandFactory = $symfonyCliCommandFactory;
        $this->cliHelper = $cliHelper;
        parent::__construct($context, $io);
    }

    /**
     * Checks if symfony CLI is installed.
     *
     * @return bool
     * @throws Exception
     */
    public function isInstalled(): bool
    {
        // @todo: run this on construct and/or cache the result?

        $command = $this->genericCommandFactory->create(['which', 'symfony']);
        $process = $command->getProcess();
        $output = $this->cliHelper->getOutput($process);
        return !empty($output);
    }

    /**
     * Runs security:check for a composer.lock.
     *
     * @param string $directory
     *
     * @return Process
     * @throws Exception
     */
    public function securityCheck($directory = ''): Process
    {
        if (empty($directory)) {
            $directory = $this->context->getComposerPath();
        }

        $command = $this->symfonyCliCommandFactory->create(
            [
                'security:check',
                '--dir=' . $directory,
            ]
        );

        return $command->getProcess();
    }
}
