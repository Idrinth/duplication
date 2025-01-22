<?php

namespace De\Idrinth\Duplication\Logger;

use De\Idrinth\Duplication\LogLevel;

readonly class CLILogger extends AbstractLogger
{
    protected function writeLog(LogLevel $level, string $message, array $context): void
    {
        $now = date('Y-m-d H:i:s');
        $addition = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "[$now][$level->name] $message\n$addition\n";
    }
}