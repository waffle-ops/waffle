<?php

namespace Waffle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

abstract class BaseTask extends BaseCommand
{

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param string|null $name
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        string $name = null
    ) {
        parent::__construct($context, $io, $name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // All tasks can accept a working directory.
        $this->addOption(
            'dir',
            null,
            InputArgument::OPTIONAL,
            'The working directory of which the task will be executed.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $this->getDirectory($input);

        if (!empty($directory)) {
            $this->context->setTaskWorkingDirectory($directory);
        }

        $exitCode = $this->process($input);

        $this->context->resetTaskWorkingDirectory();

        return $exitCode;
    }

    /**
     * Helper method to get the directory option.
     *
     * @param InputInterface $input
     * @return string|null
     */
    private function getDirectory(InputInterface $input)
    {
        $directory = null;

        $config = $this->context->get($this->getName());

        if (isset($config['dir'])) {
            $directory = $config['dir'];
        }

        if (!empty($input->getOption('dir'))) {
            $directory = $input->getOption('dir');
        }

        return $directory;
    }
}
