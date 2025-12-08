<?php

require __DIR__.'/../vendor/autoload.php';

use Core\Database;
use Core\Migrator;

$db = Database::getInstance()->connection();

$migration = new Migrator($db, __DIR__.'/Migrations');

$cmd = $argv[1] ?? 'migrate';

if ($cmd === 'migrate') {
    $migration->run();
}

if ($cmd === 'rollback') {
    $steps = $argv[2] ?? 1;
    $migration->rollback((int)$steps);
}
