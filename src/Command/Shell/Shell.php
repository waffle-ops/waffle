<?php

namespace Waffle\Command\Shell;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Waffle\Command\BaseCommand;

class Shell extends BaseCommand
{

    public const COMMAND_KEY = 'shell:command';

    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Syncs the local site from the specified upstream.');
        $this->setHelp('Syncs the local site from the specified upstream.');

        // Shortcuts would be nice, but there seems to be an odd bug as of now
        // when using dashes: https://github.com/symfony/symfony/issues/27333
        $this->addArgument(
            'cmd',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Command that the process will be running.'
        );

        // TODO Expand the help section.
        // TODO Dynamically load in the upstream options from the config file.
        // TODO Validate the upstream option from the config file (in help).
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $cmd = $input->getArgument('cmd');

        $process = new Process($cmd);
        $process->run();
        $process_output = $process->getOutput();
        
        // TODO Handle output. Can it be steamed? Or do we actually have to
        // wait until process completes? What happens in the case of something
        // like 'drush pmu' where the '-y' is ommitted?

        return Command::SUCCESS;
    }
}
