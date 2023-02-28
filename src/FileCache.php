<?php

namespace De\Idrinth\S3Duplication;

final class FileCache
{
    private $cache = [];
    public function exists(string $identifier, string $file): bool
    {
        if (isset($this->cache[$identifier]) && in_array($file, $this->cache[$identifier])) {
            return true;
        }
        return file_exists(dirname(__DIR__) . '/cache/' . md5($identifier) . '/' . md5($file));
    }
    public function load(string $identifier, string $file): string
    {
        return file_get_contents(dirname(__DIR__) . '/cache/' . md5($identifier) . '/' . md5($file)) ?: '';
    }
    public function save(string $identifier, string $file, string $data): void
    {
        $this->cache[$identifier] = $this->cache[$identifier] ?? [];
        $this->cache[$identifier][] = $file;
        mkdir(dirname(__DIR__) . '/cache/' . md5($identifier), 0777, true);
        file_put_contents(dirname(__DIR__) . '/cache/' . md5($identifier) . '/' . md5($file), $data);
    }
    public function __destruct()
    {
        foreach ($this->cache as $folder => $files) {
            foreach ($files as $file) {
                unlink(dirname(__DIR__) . '/cache/' . md5($folder) . '/' . md5($file));
            }
            rmdir(dirname(__DIR__) . '/cache/' . md5($folder));
        }
    }
}
