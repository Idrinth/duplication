<?php

namespace De\Idrinth\Duplication;

interface Downloader
{
    /**
     * @return string[]
     */
    public function list(): array;
    public function get(string $path): string;
}
