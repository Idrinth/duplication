<?php

namespace De\Idrinth\S3Duplication;

interface Uploader
{
    /**
     * @return string[]
     */
    public function list(): array;
    public function put(string $path, string $data): void;
}
