#!/bin/bash

PHAR=./scripts/build/phar.php
BUILD=dist/waffle.phar

if [ ! -f "$PHAR" ]; then
    echo "Unable to find PHPCS at $PHAR."
fi

# Reset composer files.
rm -rf vendor
composer install --prefer-dist --no-dev

# Build the phar.
php $PHAR

# Spits out a has of the built file.
sha1sum $BUILD

# Make phar executable for easy testing.
chmod 755 $BUILD

# Smoke test to see if it "works".
$BUILD --version
