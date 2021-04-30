<?php

namespace Waffle\Model\Audit;

interface AuditCheckInterface
{
    /**
     * Checks if a audit check is applicable or relavent.
     *
     * Returns true if relavent, false otherwise.
     *
     * @return bool
     */
    public function isApplicable(): bool;

    /**
     * Performs the audit check.
     *
     * Returns true is the audit check is successful, false otherwise.
     *
     * @return bool
     */
    public function validate(): bool;

    /**
     * Gets the description of the audit check.
     */
    public function getDescription(): string;

    /**
     * Gets the resolution reason for a failed audit check.
     */
    public function getResolution(): string;
}
