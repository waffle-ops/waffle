<?php

namespace Waffle\Model\Validate\Preflight;

use Symfony\Component\Validator\Validation;
use Waffle\Traits\ConfigTrait;

class PreflightValidator
{
    use ConfigTrait;

    /**
     * Runs some validation checks to ensure Waffle bootstraps properly.
     *
     * @throws MissingConfigFileException
     * @throws AmbiguousConfigException
     *
     * @return void
     */
    public function runChecks()
    {
        // This will throw exceptions if the config file cannot be loaded.
        $config = $this->getConfig();

        // TODO: Add checks for 'obvious' things such as:
        // - The cms is Drupal, but no alias exists
        // - The default_upstream config is not in upstream config.
        // - The upstream argument is not in upstreams config.
        // - etc...
        //
        // Throw exceptions as needed.
    }
}
