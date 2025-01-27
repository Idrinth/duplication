<?php

namespace De\Idrinth\Duplication;

interface Uploader
{
    public function put(string $path, string $data): void;
}
