#!/bin/bash 

PHPCS=./vendor/bin/phpcs

if [ ! -f "$PHPCS" ]; then
    echo "Unable to finde PHPCS at $PHPCS. Make sure that you are in the root of the Waffle directory and that dev dependencies have been installed via composer."
fi

$PHPCS --standard=PSR1,PSR2,PSR12 src