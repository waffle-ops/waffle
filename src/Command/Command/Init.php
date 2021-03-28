<?php

namespace Waffle\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;
use Waffle\Application as Waffle;
use Waffle\Command\BaseCommand;
use Waffle\Command\DiscoverableCommandInterface;
use Waffle\Model\Config\Item\Cms;
use Waffle\Model\Config\Item\Host;
use Waffle\Model\Config\Loader\ProjectConfigLoader;

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

        // TODO - Extend options (and command as a whole) as mre features are implemented.

        $this->addOption(
            Cms::KEY,
            null,
            InputArgument::OPTIONAL,
            'The cms used for this project (drupal7, wordpress, ect...)',
            null
        );

        $this->addOption(
            Host::KEY,
            null,
            InputArgument::OPTIONAL,
            'The hosting provider used for this project (acquia, pantheon, ect...)',
            null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
    {
        if ($this->hasExistingConfig()) {
            $this->io->warning('Waffle config file already exists. If you continue, it will be overwitten!');
            if (!$this->io->confirm('Would you like to continue?', false)) {
                return Command::SUCCESS;
            }
        }

        $initConfig = [
            Cms::KEY => $this->getCms($input),
            Host::KEY => $this->getHost($input),
        ];

        $this->io->highlightText('Writing %s config file!', [ProjectConfigLoader::CONFIG_FILE]);

        $yaml = Yaml::dump($initConfig);
        file_put_contents(ProjectConfigLoader::CONFIG_FILE, $yaml);

        $this->io->styledText('Done!');
        $this->io->styledText('For more information about the config file, go check out the Waffle documentation!');
        $this->io->highlightText('Here is a link: %s', [Waffle::DOCS_URL]);

        return Command::SUCCESS;
    }

    /**
     * Helper method for checking for existing config.
     *
     * @return bool
     */
    private function hasExistingConfig()
    {
        return $this->context->hasProjectConfig();
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
        $cms = $input->getOption(Cms::KEY);

        if (empty($cms)) {
            $cms = $this->io->choice(
                'What CMS is this project using?',
                Cms::OPTIONS
            );
        }

        // Issue a warning if the cms is not officially supported
        if ($cms === Cms::OPTION_OTHER) {
            $this->io->note([
                'You have chosen a CMS type of \'' . Cms::OPTION_OTHER . '\'.',
                'You will need to implement custom tasks and recipes in order to make your project work with Waffle.',
            ]);
        }

        return $cms;
    }

    /**
     * Helper method for initializing the host config key.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function getHost(InputInterface $input)
    {
        $host = $input->getOption(Host::KEY);

        if (empty($host)) {
            $host = $this->io->choice(
                'What hosting provider is this project using ? ',
                Host::OPTIONS
            );
        }

        // Issue a warning if the host is not officially supported.
        if ($host === Host::OPTION_OTHER) {
            $this->io->note([
                'You have chosen a host type of \'' . Cms::OPTION_OTHER . '\'.',
                'You will need to implement custom tasks and recipes in order to make your project work with Waffle.',
            ]);
        }

        return $host;
    }
}
