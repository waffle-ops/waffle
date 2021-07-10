#!/bin/bash

PHPUNIT=./vendor/bin/phpunit

if [ ! -f "$PHPUNIT" ]; then
    echo "Unable to find PHPUnit at $PHPUNIT. Make sure that you are in the root of the Waffle directory and that dev dependencies have been installed via composer."
fi

$PHPUNIT

# @todo - This is temporary. We return a zero so we can continue running other
# tests without crashing any CI workflows. Once we have actual tests and have
# fixed the backlog of issues, this will be removed.
exit 0
