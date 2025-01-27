<?php

namespace De\Idrinth\Duplication\Downloader;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\FileSystem;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Minifier;

final readonly class LocalDownloader implements Downloader
{
    public function __construct(
        private Logger $logger,
        private Encrypter $encrypter,
        private Cache $cache,
        private Filesystem $filesystem,
        private Minifier $minifier,
        private string $path,
    ) {
    }

    public function get(string $path): string
    {
        $this->logger->info("Downloading $path");
        if (!$this->cache->exists($this->path, $path)) {
            $data = $this->encrypter->encrypt(
                $this->filesystem->read(
                    $this->path . $path
                ),
            );
            $this->cache->save($this->path, $path, $this->minifier->minify($data));
            return $data;
        }
        return $this->cache->load($this->path, $path);
    }

    private function scan(string $directory, array &$output): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach ($this->filesystem->scanDir($directory) as $file) {
            if ($this->filesystem->isDir($directory . '/' . $file)) {
                $this->scan($directory . '/' . $file, $output);
            } else {
                $output[] = ltrim(
                    preg_replace(
                        '/^' . preg_quote($this->path, '/') . '/',
                        '',
                        $directory . '/' . $file
                    ),
                    '/'
                );
            }
        }
    }

    public function list(): array
    {
        $this->logger->info("Getting objects from source $this->path");
        $output = [];
        $this->scan($this->path, $output);
        $this->logger->info("Found " . count($output) . " objects.");
        return $output;
    }
}
