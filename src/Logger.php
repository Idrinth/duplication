<?php

namespace De\Idrinth\Duplication;

interface Logger
{
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
}