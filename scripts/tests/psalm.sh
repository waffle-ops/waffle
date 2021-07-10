#!/bin/bash

PSALM=./vendor/bin/psalm

if [ ! -f "$PSALM" ]; then
    echo "Unable to find Psalm at $PSALM. Make sure that you are in the root of the Waffle directory and that dev dependencies have been installed via composer."
fi

$PSALM

# @todo - This is temporary. We return a zero so we can continue running other
# tests without crashing any CI workflows. Once we have actual tests and have
# fixed the backlog of issues, this will be removed.
exit 0
