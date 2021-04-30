<?php

namespace Waffle\Model\Audit;

use Waffle\Helper\DiHelper;
use Waffle\Model\Audit\AuditManager;

class AuditService
{

    /**
     * @var DiHelper
     */
    private $diHelper;

    /**
     * Constructor
     *
     * @param DiHelper $diHelper
     */
    public function __construct(
        DiHelper $diHelper
    ) {
        $this->diHelper = $diHelper;
    }

    /**
     * Gets the audit checks.
     *
     * The checks are loaded from the container this way so that we do not load
     * the audit checks unless explicitly asked for via the audit command.
     */
    private function getAuditChecks()
    {
        $auditManager = $this->diHelper->getContainer()->get(AuditManager::class);
        return $auditManager->getAuditChecks();
    }

    /**
     * Validates all applicable audit checks and returns a report.
     *
     * @return array
     */
    public function getAuditReport()
    {
        $auditChecks = $this->getAuditChecks();

        $report = [];

        foreach ($auditChecks as $auditCheck) {
            if (!$auditCheck->isApplicable()) {
                continue;
            }

            $success = $auditCheck->validate();

            if ($auditCheck->validate()) {
                $key = $auditCheck->getDescription();
            } else {
                $key = $auditCheck->getResolution();
            }

            $report[$key] = $success;
        }

        return $report;
    }
}
