<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use dpi\DrupalPhpunitBootstrap\Utility;

$loader = require __DIR__ . '/../vendor/autoload.php';
\assert($loader instanceof ClassLoader);

foreach ($loader->getPrefixesPsr4() as $prefix => $paths) {
    // Some directories dont exist. E.g the drupal/core subtree split project we bring in references
    // path ("Drupal\\Driver\\": "../drivers/lib/Drupal/Driver") outside of its repository.
    $paths = array_filter($paths, function (string $path): bool {
        return is_dir($path);
    });
    $loader->setPsr4($prefix, $paths);
}

$dirs = [];
foreach ([
             __DIR__ . '/../vendor/drupal/core/modules',
             __DIR__ . '/../vendor/drupal/core/profiles',
             __DIR__ . '/../vendor/drupal/core/themes',
         ] as $dir) {
    $dirs = array_merge($dirs, Utility::drupal_phpunit_find_extension_directories($dir));
}

foreach (Utility::drupal_phpunit_get_extension_namespaces($dirs) as $prefix => $paths) {
    $loader->addPsr4($prefix, $paths);
}

// PSR-0: Similar to drupal/core/tests/bootstrap.php
$loader->add('Drupal\\Tests', __DIR__ . '/../vendor/drupal/core/tests');
