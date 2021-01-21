<?php

namespace Waffle\Helper;

use Phar;

class PharHelper
{

    /**
     * Returns true if application is running via a PHAR file; false otherwise.
     *
     * @return boolean
     */
    public static function isPhar()
    {
        return !empty(Phar::running());
    }
}
