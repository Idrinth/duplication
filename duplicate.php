<?php

use De\Idrinth\Duplication\Command;

require_once ('vendor/autoload.php');

ini_set('memory_limit', 0);

(new Command())->run();
