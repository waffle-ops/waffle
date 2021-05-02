<?php

namespace Waffle\Model\Audit;

use Waffle\Model\Context\Context;

abstract class BaseAuditCheck implements AuditCheckInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(): bool
    {
        // Marking all audit checks applicable, unless overridden.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        // Marking all audit checks required, unless overridden.
        return true;
    }
}
