<?php

use De\Idrinth\Duplication\Command\Setup;
use De\Idrinth\Duplication\Logger\CLILogger;
use De\Idrinth\Duplication\LogLevel;
use De\Idrinth\Duplication\RandomString\Alphanumeric;

require __DIR__ . '/../vendor/autoload.php';

(new Setup(
    new CLILogger(LogLevel::INFO),
    new Alphanumeric(),
    __DIR__ . '/../.env'
))->run();