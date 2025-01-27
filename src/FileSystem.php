<?php

namespace De\Idrinth\Duplication;

interface FileSystem
{
    public function mkdir(string $path, int $mode = 0777): void;
    public function write(string $file, string $content): void;
    public function read(string $file): string;
    public function chowngroup(string $file, string $owner, string $group): void;
    public function isDir(string $path): bool;
    public function scanDir(string $path): array;
    public function isFile(string $path): bool;
    public function delete(string $path): void;
}