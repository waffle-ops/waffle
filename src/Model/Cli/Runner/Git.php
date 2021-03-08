<?php

namespace Waffle\Model\Cli\Runner;

use Exception;
use Symfony\Component\Process\Process;
use Waffle\Helper\CliHelper;
use Waffle\Model\Cli\BaseCliRunner;
use Waffle\Model\Cli\GitCommand;

class Git extends BaseCliRunner
{

    /**
     * Adds all pending changes to index.
     *
     * @return Process
     * @throws Exception
     */
    public function addAll(): Process
    {
        $command = new GitCommand(['add', '-A']);
        return $command->getProcess();
    }

    /**
     * Check git status.
     *
     * @return Process
     * @throws Exception
     */
    public function statusShort(): Process
    {
        $command = new GitCommand(['status', '--short']);
        return $command->getProcess();
    }

    /**
     * Check if current git repo has any pending uncommitted changes.
     *
     * @return bool
     * @throws Exception
     */
    public function hasPendingChanges(): bool
    {
        $process = $this->statusShort();
        $process->run();
        return !empty($process->getOutput());
    }

    /**
     * Commit staged changes.
     *
     * @param string $message
     *
     * @return Process
     * @throws Exception
     */
    public function commit(string $message): Process
    {
        if (empty($message)) {
            throw new Exception('Git commit message is required.');
        }

        $command = new GitCommand(['commit', "--message={$message}"]);
        return $command->getProcess();
    }

    /**
     * Check if a branch exists locally.
     *
     * @param string $branch
     *
     * @return bool
     * @throws Exception
     */
    public function branchExists(string $branch): bool
    {
        if (empty($branch)) {
            return false;
        }

        $process = $this->branchList($branch);
        $cliHelper = new CliHelper();
        $output = $cliHelper->getOutput($process);

        return strpos($output, $branch) !== false;
    }

    /**
     * Get a list of local git branches.
     *
     * @param string|null $branch
     *
     * @return Process
     * @throws Exception
     */
    public function branchList(string $branch = null): Process
    {
        $args = ['branch', '--list'];
        if (!empty($branch)) {
            $args[] = $branch;
        }

        $command = new GitCommand($args);
        return $command->getProcess();
    }

    /**
     * Checkout a branch.
     *
     * @param string $branch
     * @param bool $isNew
     *
     * @return Process
     * @throws Exception
     */
    public function checkout(string $branch, bool $isNew = false): Process
    {
        if (empty($branch)) {
            throw new Exception('Git branch name is required.');
        }

        $args = ['checkout'];
        if ($isNew) {
            $args[] = '-b';
        }
        $args[] = $branch;

        $command = new GitCommand($args);
        return $command->getProcess();
    }

    /**
     * Fetch from upstreams.
     *
     * @return Process
     * @throws Exception
     */
    public function fetch(): Process
    {
        $command = new GitCommand(['fetch']);
        return $command->getProcess();
    }

    /**
     * Check if there are pending commits that should be pulled down.
     *
     * @return bool
     * @throws Exception
     */
    public function hasUpstreamPending(): bool
    {
        $command = new GitCommand(['rev-list', 'HEAD...', '--count']);
        $process = $command->getProcess();
        $cliHelper = new CliHelper();
        $output = (int) $cliHelper->getOutput($process);
        return !empty($output);
    }

    /**
     * Get the name of the current branch.
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentBranch(): string
    {
        $command = new GitCommand(['branch', '--show-current']);
        $process = $command->getProcess();
        $cliHelper = new CliHelper();
        return trim($cliHelper->getOutput($process));
    }
}
