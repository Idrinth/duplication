<?php

use De\Idrinth\Duplication\Command;
use Dotenv\Dotenv;

require_once ('vendor/autoload.php');

ini_set('memory_limit', -1);

Dotenv::createImmutable(__DIR__)->load();

(new Command())->run();
