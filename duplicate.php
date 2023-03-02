<?php

use De\Idrinth\Duplication\Command;

require_once ('vendor/autoload.php');

ini_set('memory_limit', -1);

(new Command())->run();
