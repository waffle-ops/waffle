#!/usr/bin/env bats

setup() {
    load '../common.bash'
    _common_setup
}

@test "docs --no-browser" {
  run $WFL docs --no-browser
  assert_success
  assert_output --partial 'https://github.com/waffle-ops/waffle/wiki'
}

@test "docs (with browser)" {
  # TODO Create some sort of flag to make this optional. The act of opening the
  # browser is nice for users, but annoying when running tests. Would be a good
  # idea to skip this test / emit a warning if open/xdg-open are not present.
  skip 'Not running with browser support'
  run $WFL docs
  assert_success
  assert_output --partial 'https://github.com/waffle-ops/waffle/wiki'
}

@test "docs (invalid arguments)" {
  run $WFL docs no-browser
  assert_failure
}

@test "docs (invalid option)" {
  run $WFL docs --fake-option
  assert_failure
}
