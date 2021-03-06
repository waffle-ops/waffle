<?php

namespace Waffle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Waffle\Helper\CliHelper;
use Waffle\Traits\ConfigTrait;

class Task extends BaseCommand
{
    use ConfigTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // TODO: Help and description are not properly set since these are
        // populated. Consider allow help and description text to be set in
        // config.
        $help = 'Custom Task -- <comment>See Waffle config file.</comment>';
        $this->setDescription($help);
        $this->setHelp($help);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfig();

        // Note: The 'command' argument is defined by the Symfony Command class.
        $task_key = $input->getArgument('command');

        $config_tasks = $config->getTasks() ?? [];
        $task = isset($config_tasks[$task_key]) ? $config_tasks[$task_key] : '';
        $output->writeln('<info>Running task <comment>' . $task_key . '</comment>: "' . $task . '"</info>');

        // TODO: Would be wise to add some sort of validation here.

        // TODO: I'm not a huge fan of using the shell command line method.
        // Would be better id this used an input array.
        $process = Process::fromShellCommandline($task);
        $process->setTimeout($config->getTimeout());
        $process->run();

        // TODO Handle output. Can it be streamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?

        $cliHelper = new CliHelper($this->io);
        if ($process->isSuccessful()) {
            $output->writeln('<info>Task <comment>' . $task_key . '</comment> ran sucessfully</info>');

            if (!empty($process->getOutput())) {
                $output->writeln($cliHelper->getOutput($process, false));
            }

            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Task ' . $task_key . ' returned with an error.</error>');
            $output->writeln('<error>' . $process->getOutput() . '</error>');
            $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
            return Command::FAILURE;
        }
    }
}
