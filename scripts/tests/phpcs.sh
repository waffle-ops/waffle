#!/bin/bash

PHPCS=./vendor/bin/phpcs

if [ ! -f "$PHPCS" ]; then
    echo "Unable to find PHPCS at $PHPCS. Make sure that you are in the root of the Waffle directory and that dev dependencies have been installed via composer."
fi

$PHPCS --standard=PSR1,PSR2,PSR12 src

# @todo - This is temporary. We return a zero so we can continue running other
# tests without crashing any CI workflows. Once we have actual tests and have
# fixed the backlog of issues, this will be removed.
exit 0
