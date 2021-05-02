<?php

namespace Waffle\Command\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Waffle\Command\BaseTask;
use Waffle\Command\DiscoverableTaskInterface;
use Waffle\Model\Audit\AuditService;
use Waffle\Model\Context\Context;
use Waffle\Model\IO\IOStyle;

class Audit extends BaseTask implements DiscoverableTaskInterface
{
    public const COMMAND_KEY = 'audit';

    /**
     * @var AuditCheckInterface[]
     *
     * A list of audit checks that are required to pass.
     */
    private $requiredAuditChecks = [];

    /**
     * @var AuditCheckInterface[]
     *
     * A list of audit checks that are not required to pass.
     */
    private $recommendedAuditChecks = [];

    /**
     * Constructor
     *
     * @param Context $context
     * @param IOStyle $io
     * @param AuditService $auditService
     */
    public function __construct(
        Context $context,
        IOStyle $io,
        AuditService $auditService
    ) {
        $this->auditService = $auditService;
        parent::__construct($context, $io);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(self::COMMAND_KEY);
        $this->setDescription('Performs audit checks against the project.');
        $this->setHelp('Performs audit checks against the project.');
    }

    /**
     * {@inheritdoc}
     */
    protected function process(InputInterface $input)
    {
        $this->io->title('Running Project Audit');

        $this->loadAuditChecks();

        $requiredFailures = $this->doRequiredAuditChecks();
        $this->io->newLine();

        $recommendedFailures = $this->doRecommendedAuditChecks();
        $this->io->newLine();

        // Displays failed required audit checks and provides details on how
        // to resolve the issues.
        $this->io->section('Required Audit Check Results:');
        $requiredResolution = $this->getResolutionTable($requiredFailures);
        if (!empty($requiredResolution)) {
            $this->io->table(
                [
                    $this->io->styleText('Failed Required Audit Check', 'status_error'),
                    $this->io->styleText('Resolution', 'status_success'),
                ],
                $requiredResolution,
                'box'
            );

            $this->io->error('Required audit checks failed! Please resolve the issues displayed above.');
        } else {
            $this->io->success('All required audit checks have passed!');
        }

        // Displays failed recommended audit checks and provides details on how
        // to resolve the issues.
        $this->io->section('Recommended Audit Check Results:');
        $recommendedResolution = $this->getResolutionTable($recommendedFailures);
        if (!empty($recommendedResolution)) {
            $this->io->table(
                [
                    $this->io->styleText('Failed Recommended Audit Check', 'status_error'),
                    $this->io->styleText('Resolution', 'status_success'),
                ],
                $recommendedResolution,
                'box'
            );

            $this->io->error(
                'Recommended audit checks failed! It is recommended that you address the issues displayed above.'
            );
        } else {
            $this->io->success('All recommended audit checks have passed!');
        }

        // Adding a new line because it looks better in the output.
        $this->io->newLine();

        if (empty($requiredFailures)) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * Loads the required and recommended audit check lists.
     *
     * @return void
     */
    private function loadAuditChecks()
    {
        $auditChecks = $this->auditService->getAuditChecks();

        foreach ($auditChecks as $auditCheck) {
            if (!$auditCheck->isApplicable()) {
                continue;
            }

            if ($auditCheck->isRequired()) {
                $this->requiredAuditChecks[] = $auditCheck;
            } else {
                $this->recommendedAuditChecks[] = $auditCheck;
            }
        }
    }

    /**
     * Validates required audit checks and returns a list of failed checks.
     *
     * @return AuditCheckInterface[]
     */
    private function doRequiredAuditChecks()
    {
        $failures = [];

        if (empty($this->requiredAuditChecks)) {
            return $failures;
        }

        $this->io->section('Running Required Checks');
        foreach ($this->requiredAuditChecks as $auditCheck) {
            if ($auditCheck->validate()) {
                $this->io->statusSuccess($auditCheck->getDescription(), 'x');
            } else {
                $this->io->statusError($auditCheck->getDescription(), ' ');

                $failures[] = $auditCheck;
            }
        }

        return $failures;
    }

    /**
     * Validates recommended audit checks and returns a list of failed checks.
     *
     * @return AuditCheckInterface[]
     */
    private function doRecommendedAuditChecks()
    {
        $failures = [];

        if (empty($this->recommendedAuditChecks)) {
            return $failures;
        }

        $this->io->section('Running Recommended Checks');
        foreach ($this->recommendedAuditChecks as $auditCheck) {
            if ($auditCheck->validate()) {
                $this->io->statusSuccess($auditCheck->getDescription(), 'x');
            } else {
                // Using a warning instead of an error since this technically
                // isn't required.
                $this->io->statusWarning($auditCheck->getDescription(), ' ');

                $failures[] = $auditCheck;
            }
        }

        return $failures;
    }

    /**
     * Displays the required resolution table.
     *
     * @return array
     */
    private function getResolutionTable($auditChecks)
    {
        $resolutions = [];

        foreach ($auditChecks as $auditCheck) {
            $resolutions[] = [
                $auditCheck->getDescription(),
                $auditCheck->getResolution(),
            ];
        }

        return $resolutions;
    }
}
