<?php

namespace Waffle\Model\Audit;

class AuditManager
{

    /**
     * @var array
     *
     * Avaliable audit checks for the Waffle application.
     */
    private $auditChecks = [];

    /**
     * Constructor
     *
     * @param iterable
     *   Audit checks configured in the DI container.
     */
    public function __construct(iterable $auditChecks = [])
    {
        $this->auditChecks = $auditChecks;
    }

    /**
     * Gets the audit checks.
     */
    public function getAuditChecks()
    {
        return $this->auditChecks;
    }
}
