<?php

namespace De\Idrinth\Duplication\Downloader;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;

final class LocalDownloader implements Downloader
{
    private string $prefix = '';
    private string $datePrefix = '';

    public function __construct(
        private readonly Logger $logger,
        private readonly Encrypter $encrypter,
        private readonly Cache $cache,
        bool $hasMultipleDailyBackups,
        private readonly string $path,
        string $prefix,
        bool $forceDatePrefix,
        private readonly bool $encrypt
    ) {
        $this->prefix = $prefix ?: basename($path);
        if ($forceDatePrefix) {
            $this->datePrefix = '/' . date('Y-m-d') . ($hasMultipleDailyBackups ? date('-H') : '');
        }
    }

    public function get(string $path): string
    {
        $this->logger->info("Downloading $path");
        if (!$this->cache->exists($this->path, $path)) {
            $data = $this->encrypter->encrypt(
                file_get_contents($this->path . preg_replace('/^' . preg_quote($this->prefix . $this->datePrefix, '/') . '/', '', $path)) ?: '',
                $this->encrypt
            );
            $this->cache->save($this->path, $path, $data);
            return $data;
        }
        return $this->cache->load($this->path, $path);
    }

    private function scan(string $directory, array &$output): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (array_diff(scandir($directory), ['.', '..']) as $file) {
            if (is_dir($directory . '/' . $file)) {
                $this->scan($directory . '/' . $file, $output);
            } else {
                $output[] = $this->prefix . $this->datePrefix . '/' . ltrim(preg_replace('/^' . preg_quote($this->path, '/') . '/', '', $directory . '/' . $file), '/');
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
