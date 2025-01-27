<?php

namespace De\Idrinth\Duplication;

use InvalidArgumentException;

final readonly class LogLevelFactory
{
    public function convert($level): LogLevel
    {
        if (is_string($level)) {
            return match (strtolower($level)) {
                'none' => LogLevel::NONE,
                'info' => LogLevel::INFO,
                'warning' => LogLevel::WARNING,
                'error' => LogLevel::ERROR,
                default => throw new InvalidArgumentException("Log level '$level' is not supported."),
            };
        }
        if (is_int($level)) {
            return match ($level) {
                0 => LogLevel::NONE,
                1 => LogLevel::INFO,
                2 => LogLevel::WARNING,
                3 => LogLevel::ERROR,
                default => throw new InvalidArgumentException("Log level '$level' is not supported."),
            };
        }
        $type = gettype($level);
        throw new InvalidArgumentException("Log level type '$type' is not supported.");
    }
}