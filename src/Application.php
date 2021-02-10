<?php

namespace Waffle;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Waffle\Exception\Config\MissingConfigFileException;
use Waffle\Exception\UpdateCheckException;
use Waffle\Model\Command\CommandManager;
use Waffle\Model\Validate\Preflight\PreflightValidator;
use Waffle\Model\IO\IO;
use Waffle\Model\IO\IOStyle;
use Waffle\Helper\PharHelper;
use Waffle\Helper\GitHubHelper;
use Waffle\Helper\WaffleHelper;

class Application extends SymfonyApplication
{
    public const NAME = 'Waffle';

    public const VERSION = 'v0.0.1-alpha';

    public const REPOSITORY = 'waffle-ops/waffle';

    public const EMOJI_POOL = [
        // Older emojis that should be supported everywhere.
        "\u{1F353}", // Strawberry
        "\u{1F953}", // Bacon
        "\u{1F95A}", // Egg

        // TODO: The below emojis were added in 2019, but are not widely
        // supported yet. Eventually I would like to have these two replace the
        // pool above (or at the very least add to the pool).
        // "\u{1F9C7}", // Waffle
        // "\u{1F9C8}", // Butter
    ];

    /**
     * @var CommandManager
     *
     * Command manager for the Waffle application.
     */
    private $commandManager;

    /**
     * Defines the Input/Output helper object.
     *
     * @var IOStyle
     */
    protected $io;

    public function __construct()
    {
        // Adding some emoji flair for fun.
        $emoji = array_rand(array_flip(self::EMOJI_POOL), 1);
        $name = sprintf('%s %s', $emoji, self::NAME);
        parent::__construct($name, self::VERSION);

        $this->io = IO::getInstance()->getIO();

        // Prevent auto exiting (so we can run extra code).
        $this->setAutoExit(false);
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $passed_preflight_checks = true;
        $missing_config = false;

        try {
            $validator = new PreflightValidator();
            $validator->runChecks();
        } catch (MissingConfigFileException $e) {
            $passed_preflight_checks = false;
            $missing_config = true;
        } catch (\Exception $e) {
            $passed_preflight_checks = false;
        }

        // Most exceptions should prevent Waffle commands from loading.
        if ($passed_preflight_checks && !empty($this->commandManager)) {
            $this->addCommands($this->commandManager->getCommands());
        }

        if ($missing_config) {
            // TODO: Attach some sort of 'init' command that can help guide
            // users in creating a .waffle.yml file.
            $this->io->writeln('<error>No .waffle.yml file was found!</error>');
            $this->io->writeln('<error>Waffle can\'t do much without knowing more about your project.</error>');
        }

        $exitCode = parent::run();

        // Update notices will be after all other output so that they don't get
        // lost.
        $this->checkVersion();

        exit($exitCode);
    }

    /**
     * Sets the command manager for the application.
     *
     * @param CommandManager $commandManager
     *   The command manager for the application.
     *
     * @return void
     */
    public function setCommandManager(CommandManager $commandManager)
    {
        $this->commandManager = $commandManager;
    }

    /**
     * Overrides the find method to install call get.
     *
     * The default behavior of the application is to call a best guess if it is
     * reasonibly sure it is correct. For example, if a 'phpcs' command existed
     * and the user only typed 'phpc', the base code is smart enough to run
     * 'phpcs' instead.
     *
     * We are suppressing the above behavior because we are creating tasks in a
     * non-standard way. The tasks and recipes defined in the config file do
     * not get picked up properly. This may be revisited, but for now, I think
     * it best to force a hard fail if a command is mistyped.
     *
     * @return Command A Command instance
     *
     * @throws CommandNotFoundException When command does not exist.
     */
    public function find(string $name)
    {
        return $this->get($name);
    }

    /**
     * Checks current version against the latest version of the application.
     * Emits a notice if a pending upate is found.
     *
     * @return void
     */
    private function checkVersion()
    {
        // TODO: Consider option to suppress update notices.
        $latest = $this->getLatestReleaseVersion();

        if (self::VERSION === $latest) {
            // No reason to continue.
            return;
        }

        $this->io->title('Update Avaliable!');

        $notice = 'You are using an outdated version of Waffle!';
        $notice .= str_repeat(PHP_EOL, 2);
        $notice .= sprintf('You can upgrade to the latest release (%s) by ', $latest);

        if (PharHelper::isPhar()) {
            $notice .= 'running the \'self:update\' command.';
        } else {
            $notice .= 'pulling latest copy of the code and following the install instructions ';
            $notice .= 'or by installing the .phar file.';
            $notice .= str_repeat(PHP_EOL, 2);

            $notice .= 'Installing via the .phar file is recommended as you can use the \'self:update\' ';
            $notice .= 'command for future updates.';
        }

        $this->io->note($notice);

        // TODO Add changelogs? It may entice some users to upgrade.

        // TODO: Running self:update instead of emitting a warning may be wise
        // in the future (stable release time). I like the idea of the tool
        // installing an update when found. That would force adoption. Before
        // going down that road, we should define api versions or something so
        // that we don't do any major harm. For now, it needs to be manual
        // since there is no defined api and we may be releasing breaking
        // changes until things are settled.
    }

    /**
     * Gets the latest release version of Waffle. Will call out to GitHub for
     * the latest tag if not cached.
     *
     * @throws UpdateCheckException
     *
     * @return string
     */
    private function getLatestReleaseVersion()
    {
        // There is really no need to call out to GitHub for this update check
        // on every command run. So, we are caching the details we need and
        // will call out to GitHub at most three times per day (unless the
        // cache is cleared).
        $helper = new WaffleHelper();
        $data = $helper->getCacheData('update_check');

        if (!empty($data['last_check']) && !empty($data['latest_release'])) {
            $time_diff = time() - $data['last_check'];

            // If less than 8 hours, we can skip.
            if ($time_diff < 28800) {
                return $data['latest_release'];
            }
        }

        // It has been a while since we last checked, so calling out to GitHub.
        try {
            $githubHelper = new GitHubHelper();
            $release = $githubHelper->getLatestRelease(self::REPOSITORY);
            $latest = $release['tag_name'];

            // Store the latest version so we can limit how often we reach out
            // to GitHub.
            $data = [
                'last_check' => time(),
                'latest_release' => $latest,
            ];

            $helper->setCacheData('update_check', $data);
        } catch (UpdateCheckException $e) {
            // TODO: We should probably have some sort of log file where we can
            // log this type of failure.
            // return;
            throw $e;
        }

        return $latest;
    }
}
