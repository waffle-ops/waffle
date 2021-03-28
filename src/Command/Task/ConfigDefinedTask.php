<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseTask;
use Waffle\Helper\CliHelper;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class ConfigDefinedTask extends BaseTask
{
    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param CliHelper $cliHelper
     * @param string $taskKey
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        CliHelper $cliHelper,
        string $taskKey
    ) {
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

        // TODO: I'm not a huge fan of using the shell command line method.
        // Would be better id this used an input array.
        $process = Process::fromShellCommandline($task);
        $process->setTimeout($this->context->getTimeout());
        $process->run();

        // TODO Handle output. Can it be streamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?

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
