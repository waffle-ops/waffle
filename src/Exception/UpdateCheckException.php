<?php

namespace Waffle\Exception;

use Exception;

class UpdateCheckException extends Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
