#!/usr/bin/env bats

setup() {
    load '../common.bash'
    _common_setup
    _phar_setup
}

teardown() {
  _phar_teardown
}

@test "self-update not present outside phar" {
  run $WFL list
  assert_success
  refute_output --partial 'self-update'
}

@test "self-update present in phar" {
  run $WAFFLE_PHAR list
  assert_success
  assert_output --partial 'self-update'
}

@test "self-update download" {
  ORIGINAL_VERSION=$($WAFFLE_PHAR --version)

  run $WAFFLE_PHAR self-update
  assert_success

  if [ "$output" = "No update available" ]; then
    skip "No update available"
  fi

  # Make sure we can run the new phar.
  run $WAFFLE_PHAR --version
  assert_success
  refute_output "$ORIGINAL_VERSION"

  run $WAFFLE_PHAR list
  assert_success
  assert_output --partial 'self-update'
}
