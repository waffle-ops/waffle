<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Model\Config\ProjectConfig;
use Symfony\Component\Console\Input\InputArgument;

class Init extends BaseCommand implements DiscoverableCommandInterface
{
    public const COMMAND_KEY = 'init';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Guides you through creating a Waffle config file for your project.');

        // TODO Add optional arguments so that something like
        // wfl init --cms=drupal8 --host=custom can be done in a CI layer
        // without actuall needing to create track a .waffle.yml file.

        $this->addOption(
            ProjectConfig::KEY_CMS,
            null,
            InputArgument::OPTIONAL,
            'The cms used for this project (drupal7, wordpress, ect...)',
            null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // TODO ask if user is where they want to be?
        // TODO -- Check for .waffle.yml directory?

        $config = [
            ProjectConfig::KEY_CMS => $this->getCms($input),
        ];

        $this->io->note($config);

        return Command::SUCCESS;
    }

    /**
     * Helper method for initializing the cms config key.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function getCms(InputInterface $input)
    {
        $cms = $input->getOption(ProjectConfig::KEY_CMS);

        if (empty($cms)) {
            $cmsOptions = array_merge(ProjectConfig::CMS_OPTIONS, ['other']);
            $cms = $this->io->choice(
                'What CMS is this project using?',
                $cmsOptions
            );

            if ($cms === 'other') {
                $cms = $this->io->ask('What CMS is the project using?');
            }
        }

        // Issue a warning if the cms is not officially supported.
        if (!in_array($cms, ProjectConfig::CMS_OPTIONS)) {
            $this->io->note([
                'You have chosen a CMS that is not officially supported by Waffle, and that\'s okay!',
                'You will need to implement custom tasks and recipes in order to make your project work with Waffle.',
            ]);
        }

        return $cms;
    }
}
