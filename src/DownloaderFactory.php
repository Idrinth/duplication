<?php

namespace De\Idrinth\Duplication;

interface DownloaderFactory
{
    public function getDownloader(array $config): Downloader;
}