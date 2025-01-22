<?php

use De\Idrinth\Duplication\Cache\File;
use De\Idrinth\Duplication\Command\Duplicate;
use De\Idrinth\Duplication\Encrypter\AES;
use De\Idrinth\Duplication\Logger\CLILogger;
use De\Idrinth\Duplication\Logger\FileLogger;
use De\Idrinth\Duplication\Logger\MultiLogger;
use De\Idrinth\Duplication\LogLevelFactory;
use Dotenv\Dotenv;

require_once('vendor/autoload.php');

ini_set('memory_limit', -1);

Dotenv::createImmutable(__DIR__ . '/..')->load();

(new Duplicate(
    new MultiLogger(
        new FileLogger(
            (new LogLevelFactory())->convert($_ENV['LOG_FILE_LEVEL']),
            $_ENV['LOG_FILE_PATH'],
        ),
        new CLILogger(
            (new LogLevelFactory())->convert($_ENV['LOG_CLI_LEVEL']),
        ),
    ),
    new File($_ENV['CACHE_PATH']),
    new AES(
        $_ENV['ENCRYPTION_AES_IV'],
        intval($_ENV['ENCRYPTION_AES_LENGTH'], 10),
        $_ENV['ENCRYPTION_AES_KEY'],
    ),
    __DIR__ . '/../config.yml',
    __DIR__ . '/../is-running',
    strtolower($_ENV['MULTIPLE_DAILY_BACKUPS']) === 'true',
))->run();
