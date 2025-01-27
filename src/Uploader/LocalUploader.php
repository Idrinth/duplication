<?php

namespace De\Idrinth\Duplication\Uploader;

use De\Idrinth\Duplication\DateTimePrefixer;
use De\Idrinth\Duplication\FileSystem;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;

final readonly class LocalUploader implements Uploader
{
    public function __construct(
        private Logger $logger,
        private DateTimePrefixer $dateTimePrefixer,
        private FileSystem $fileSystem,
        private string $path,
        private string $user,
        private string $group
    ) {
    }

    public function put(string $path, string $data): void
    {
        if (!$data) {
            return;
        }
        $this->logger->info("Uploading $path.");
        $file = $this->path . '/' . $this->dateTimePrefixer . ltrim($path, '/');
        $this->fileSystem->write($file, $data);
        $this->fileSystem->chowngroup($file, $this->group, $this->user);
    }
}
