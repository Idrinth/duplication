<?php

namespace De\Idrinth\Duplication\Logger;

use De\Idrinth\Duplication\LogLevel;

final readonly class FileLogger extends AbstractLogger
{
    public function __construct(LogLevel $logLevel, private string $filePath)
    {
        parent::__construct($logLevel);
    }

    protected function writeLog(LogLevel $level, string $message, array $context): void
    {
        $now = date('Y-m-d H:i:s');
        $pid = getmypid();
        $addition = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents(
            $this->filePath,
            "[$now][PID:$pid][$level->name] $message $addition\n",
            FILE_APPEND
        );
    }
}