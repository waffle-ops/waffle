<?php

$waffle_phar = 'waffle.phar';
$target_path = 'dist';
$waffle_bin = 'bin/waffle.php';

$current_directory = getcwd();
$waffle_root = realpath(__DIR__ . '/../../');

// This isn't a great check, but it will work. We just need to make sure we are
// in the root of the waffle repository.
if ($current_directory !== $waffle_root) {
    echo 'You must run this script from the root of the repository.' . PHP_EOL;
    die(1);
}

// Attempting to make sure we can write the .phar.
ini_set('phar.readonly', 0);

// If we can't write, let the user know that we can't do the build.
if (ini_get('phar.readonly')) {
    echo 'The php.ini setting \'phar.readonly\' is set to \'Off\'. This setting needs to be updated to allow the build.' . PHP_EOL;
    die(1);
}

// Everything looks good! Let's build the .phar.
$phar_destination = sprintf('%s/%s/%s', $waffle_root, $target_path, $waffle_phar);
$phar = new Phar($phar_destination, 0, $waffle_phar);
$include = '/^(?=(.*bin|.*config|.*src|.*vendor))(.*)$/i';
$phar->buildFromDirectory($waffle_root, $include);
$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub($waffle_bin));
