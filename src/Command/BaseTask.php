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
     */
    public function __construct(
        Context $context,
        IOStyle $io
    ) {
        parent::__construct($context, $io);
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
        $directory = $input->getOption('dir');

        if (!empty($directory)) {
            $this->context->setTaskWorkingDirectory($directory);
        }

        $exitCode = $this->process($input);

        $this->context->resetTaskWorkingDirectory();

        return $exitCode;
    }
}
