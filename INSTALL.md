# Install Instructions

## Install waffle.phar (Recommended)
1. Ensure you have PHP installed.
1. Ensure you have any other dependencies for your projects installed (composer, drush, etc...). These tools should be on your $PATH so that waffle can use them.
1. Download the latest waffle.phar [release page](https://github.com/waffle-ops/waffle/releases/latest)
1. Move waffle.phar to a directory that is on you $PATH (ex `/usr/local/bin/`, a bash alias, etc...)
1. Renave waffle.phar to `wfl`
1. Make sure that `wfl` is executable
1. That's it! Try running `wfl --version`.

## Install via Source
Note: Installing via source is reccomended only if you planning on modifying the code.
1. Ensure you have PHP installed.
1. Ensure you have composer installed.
1. Ensure you have any other dependencies for your projects installed (composer, drush, etc...). These tools should be on your $PATH so that waffle can use them.
1. Clone the repository
1. Run `composer install` in the project directory
1. Add the executable at `bin/wfl` to your $PATH
1. You should be all set. Try running `wfl --version`
