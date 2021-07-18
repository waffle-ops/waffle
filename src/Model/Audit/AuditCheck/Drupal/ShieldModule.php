<?php

namespace Waffle\Model\Audit\AuditCheck\Drupal;

use Symfony\Component\Finder\Finder;
use Waffle\Model\Audit\BaseAuditCheck;
use Waffle\Traits\DrupalContextTrait;

class ShieldModule extends BaseAuditCheck
{
    use DrupalContextTrait;

    /**
     * {@inheritdoc}
     */
    public function isApplicable(): bool
    {
        return $this->isDrupal();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // @todo This is not a great test, but is at least enough to show the
        // thought process around these audit checks.
        $finder = new Finder();
        $finder->files();
        $finder->in([
            getcwd(),
        ]);
        $finder->name([
            'shield.info.yml',
            'shield.info',
        ]);

        return $finder->hasResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Ensure the shield module is installed.';
    }

    /**
     * {@inheritdoc}
     */
    public function getResolution(): string
    {
        return "Install the shield module.";
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return false;
    }
}
