<?php

use De\Idrinth\Duplication\Factories\CommandFactory;

require_once('vendor/autoload.php');

CommandFactory::prepare(dirname(__DIR__))
    ->create($argv[1] ?? 'duplicate')
    ->run();