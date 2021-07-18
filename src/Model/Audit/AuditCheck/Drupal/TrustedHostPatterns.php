<?php

namespace Waffle\Model\Audit\AuditCheck\Drupal;

use Waffle\Model\Audit\BaseAuditCheck;
use Waffle\Model\Cli\Runner\Drush;
use Waffle\Model\Context\Context;
use Waffle\Traits\DrupalContextTrait;

class TrustedHostPatterns extends BaseAuditCheck
{
    use DrupalContextTrait;

    /**
     * @var Drush
     */
    private $drush;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Drush $drush
     */
    public function __construct(
        Context $context,
        Drush $drush
    ) {
        $this->drush = $drush;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(): bool
    {
        return $this->isDrupal8();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        // This is dirty, but it works. I could instead have done some PHP
        // parsing magic on the settings.php file, but this gives us the
        // advantage of letting Drush bootstrap Drupal.

        // I could have also included a php script that does more or less the
        // the same thing and called it through 'drush php-script', but that
        // seems like overkill.
        $process = $this->drush->phpEval('
            $patterns = \Drupal\Core\Site\Settings::get("trusted_host_patterns", []);
            if (empty($patterns)) {
                echo "waffle_trusted_host_patterns_unset";
            }
        ');

        $missing = strpos($process->getOutput(), 'waffle_trusted_host_patterns_unset') !== false;

        return !$missing;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Ensure $settings[\'trusted_host_patterns\'] is set.';
    }

    /**
     * {@inheritdoc}
     */
    public function getResolution(): string
    {
        return sprintf(
            "%s\n%s",
            'Add trusted_host_patterns in settings.php.',
            'See https://www.drupal.org/docs/installing-drupal/trusted-host-settings.'
        );
    }
}
