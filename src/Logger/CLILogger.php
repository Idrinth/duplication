<?php

namespace De\Idrinth\Duplication\Logger;

use De\Idrinth\Duplication\LogLevel;

final readonly class CLILogger extends AbstractLogger
{
    protected function writeLog(LogLevel $level, string $message, array $context): void
    {
        $now = date('Y-m-d H:i:s');
        echo "[$now][$level->name] $message\n";
    }
}