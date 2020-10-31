#!/bin/bash 

PHPCBF=./vendor/bin/phpcbf

if [ ! -f "$PHPCS" ]; then
    echo "Unable to finde PHPCBF at $PHPCBF. Make sure that you are in the root of the Waffle directory and that dev dependencies have been installed via composer."
fi

$PHPCBF --standard=PSR1,PSR2,PSR12 src