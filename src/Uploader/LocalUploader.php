<?php

namespace De\Idrinth\Duplication\Uploader;

use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;

final readonly class LocalUploader implements Uploader
{
    public function __construct(
        private Logger $logger,
        private string $path,
        private string $user,
        private string $group
    ) {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function put(string $path, string $data): void
    {
        if (!$data) {
            return;
        }
        $this->logger->info("Uploading $path.");
        $file = $this->path . '/' . ltrim($path, '/');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        file_put_contents($file, $data);
        chgrp($file, $this->group);
        chown($file, $this->user);
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
                $output[] = ltrim(preg_replace('/^' . preg_quote($this->path, '/') . '/', '', $directory . '/' . $file), '/');
            }
        }
    }

    public function list(): array
    {
        $this->logger->info("Getting objects from target $this->path");
        $output = [];
        $this->scan($this->path, $output);
        $this->logger->info("Found " . count($output) . " objects.");
        return $output;
    }
}
