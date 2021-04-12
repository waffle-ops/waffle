#!/usr/bin/env bash

_common_setup() {
    # Calculates the project root so we know where dependencies are.
    PROJECT_ROOT="$( cd "$( dirname "$BATS_TEST_FILENAME" )/../../../" >/dev/null 2>&1 && pwd )"

    # Putting the executable in a variable. This is done because we want to
    # ensure we are calling the version of Waffle that is under development. It
    # is possible 'wfl' is already on the $PATH, so storing it in $WFL for the
    # tests.
    WFL="$PROJECT_ROOT/bin/wfl"

    # Loading in helper packages for working with bats.
    VENDOR="$PROJECT_ROOT/vendor/bats-core"
    load "$VENDOR/bats-support/load.bash"
    load "$VENDOR/bats-assert/load.bash"
}
