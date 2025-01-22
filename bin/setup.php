<?php

use De\Idrinth\Duplication\Command\Setup;
use De\Idrinth\Duplication\RandomString\Alphanumeric;

require __DIR__ . '/../vendor/autoload.php';

(new Setup(
    new Alphanumeric(),
    __DIR__ . '/../.env'
))->run();