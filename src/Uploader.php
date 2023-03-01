<?php

namespace De\Idrinth\Duplication;

interface Uploader
{
    /**
     * @return string[]
     */
    public function list(): array;
    public function put(string $path, string $data): void;
}
