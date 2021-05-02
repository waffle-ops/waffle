<?php

namespace Waffle\Model\Audit;

use Waffle\Helper\DiHelper;

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
     *
     * @return AuditCheckInterface[]
     */
    public function getAuditChecks()
    {
        $auditManager = $this->diHelper->getContainer()->get(AuditManager::class);
        return $auditManager->getAuditChecks();
    }
}
