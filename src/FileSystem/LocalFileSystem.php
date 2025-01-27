<?php

namespace De\Idrinth\Duplication\FileSystem;

use De\Idrinth\Duplication\FileSystem;

final readonly class LocalFileSystem implements FileSystem
{
    public function mkdir(string $path, int $mode = 0777): void
    {
        mkdir($path, $mode, true);
    }

    public function write(string $file, string $content): void
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $content);
    }

    public function read(string $file): string
    {
        return file_get_contents($file) ?: '';
    }

    public function chowngroup(string $file, string $owner, string $group): void
    {
        chown($file, $owner);
        chgrp($file, $group);
    }

    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    public function scanDir(string $path): array
    {
        return array_diff(scandir($path), ['.', '..']);
    }

    public function isFile(string $path): bool
    {
         return is_file($path);
    }

    public function delete(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
            return;
        }
        foreach ($this->scanDir($path) as $file) {
            $this->delete($path.'/'.$file);
        }
        rmdir($path);
    }
}