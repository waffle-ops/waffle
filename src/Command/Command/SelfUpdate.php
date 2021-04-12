<?php

namespace Waffle\Command\Command;

use SelfUpdate\SelfUpdateCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Application as Waffle;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Helper\PharHelper;

class SelfUpdate extends SelfUpdateCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'self-update';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(Waffle::NAME, Waffle::VERSION, Waffle::REPOSITORY);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);
        } catch (\Exception $e) {
            // Intentionally blank.
        }

        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        // This command should only work for .phar files.
        return PharHelper::isPhar();
    }
}
