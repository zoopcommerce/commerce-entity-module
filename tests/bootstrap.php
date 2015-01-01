<?php

putenv('SERVER_TYPE=test');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '256M');

chdir(__DIR__ . '/../');

$loader = require __DIR__ . '/../vendor/autoload.php';

if (!isset($loader)) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$loader->add('Zoop\Entity\Test', __DIR__);
