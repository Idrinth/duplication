<?php

namespace De\Idrinth\Duplication\Logger;

use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\LogLevel;

abstract readonly class AbstractLogger implements Logger
{
    public function __construct(private LogLevel $logLevel) {}
    abstract protected function writeLog(LogLevel $level, string $message, array $context): void;
    final protected function log(LogLevel $level, string $message, array $context): void
    {
        switch ($level) {
            case LogLevel::ERROR:
                if ($this->logLevel === LogLevel::ERROR) {
                    $this->writeLog($level, $message, $context);
                }
            case LogLevel::WARNING:
                if ($this->logLevel === LogLevel::WARNING) {
                    $this->writeLog($level, $message, $context);
                }
            case LogLevel::INFO:
                if ($this->logLevel === LogLevel::INFO) {
                    $this->writeLog($level, $message, $context);
                }
            case LogLevel::NONE:
                // nothing to log
        }
    }
    final public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    final public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    final public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
}