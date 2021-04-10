<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Waffle\Command\BaseTask;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\Factory\GenericCommandFactory;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class ConfigDefinedTask extends BaseTask
{
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
     * @param string $taskKey
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        GenericCommandFactory $genericCommandFactory,
        CliHelper $cliHelper,
        string $taskKey
    ) {
        $this->genericCommandFactory = $genericCommandFactory;
        $this->cliHelper = $cliHelper;
        parent::__construct($context, $io, $taskKey);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        // TODO: Help and description are not properly set since these are
        // populated. Consider allow help and description text to be set in
        // config.
        $help = 'Custom ConfigDefinedTask -- <comment>See Waffle config file.</comment>';
        $this->setDescription($help);
        $this->setHelp($help);
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
    {
        // Note: The 'command' argument is defined by the Symfony Command class.
        $task_key = $input->getArgument('command');

        $config_tasks = $this->context->getTasks() ?? [];
        $task = isset($config_tasks[$task_key]) ? $config_tasks[$task_key] : '';
        $this->io->highlightText('Running task %s: %s', [$task_key, $task]);

        // TODO: Would be wise to add some sort of validation here.

        $process = $this->genericCommandFactory->create([$task])->getProcess();
        $process->run();

        if ($process->isSuccessful()) {
            $this->io->highlightText('Task %s ran successfully', [$task_key]);

            if (!empty($process->getOutput())) {
                $this->io->text($this->cliHelper->getOutput($process, false));
            }

            return Command::SUCCESS;
        } else {
            $this->io->text('<error>ConfigDefinedTask ' . $task_key . ' returned with an error.</error>');
            $this->io->text('<error>' . $process->getOutput() . '</error>');
            $this->io->text('<error>' . $process->getErrorOutput() . '</error>');
            return Command::FAILURE;
        }
    }
}
