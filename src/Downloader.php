<?php

namespace De\Idrinth\S3Duplication;

interface Downloader
{
    /**
     * @return string[]
     */
    public function list(): array;
    public function get($path): string;
}
