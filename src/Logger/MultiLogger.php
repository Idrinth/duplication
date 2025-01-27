<?php

namespace De\Idrinth\Duplication\Logger;

use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\LogLevel;

final readonly class MultiLogger extends AbstractLogger
{
    private array $loggers;
    public function __construct(Logger ...$loggers)
    {
        parent::__construct(LogLevel::INFO);
        $this->loggers = $loggers;
    }

    protected function writeLog(LogLevel $level, string $message, array $context): void
    {
        foreach ($this->loggers as $logger) {
            switch ($level) {
                case LogLevel::ERROR:
                    $logger->error($message, $context);
                    break;
                case LogLevel::WARNING:
                    $logger->warning($message, $context);
                    break;
                case LogLevel::INFO:
                    $logger->info($message, $context);
                    break;
                case LogLevel::NONE:
            }
        }
    }
}