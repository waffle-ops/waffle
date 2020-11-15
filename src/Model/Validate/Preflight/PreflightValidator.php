<?php

namespace Waffle\Model\Validate\Preflight;

use Symfony\Component\Validator\Validation;

class PreflightValidator
{

    public function __construct()
    {
        $validator = Validation::createValidator();
    }
}
