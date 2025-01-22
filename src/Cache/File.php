<?php

namespace De\Idrinth\Duplication\Cache;

use De\Idrinth\Duplication\Cache;

final class File implements Cache
{
    /**
     * @var string[][]
     */
    private array $cache = [];
    public function __construct(private readonly string $cachePath)
    {
    }

    public function exists(string $identifier, string $file): bool
    {
        if (isset($this->cache[$identifier]) && in_array($file, $this->cache[$identifier])) {
            return true;
        }
        return file_exists($this->cachePath. '/'  . md5($identifier) . '/' . md5($file));
    }
    public function load(string $identifier, string $file): string
    {
        return file_get_contents($this->cachePath. '/'  . md5($identifier) . '/' . md5($file)) ?: '';
    }
    public function save(string $identifier, string $file, string $data): void
    {
        $this->cache[$identifier] = $this->cache[$identifier] ?? [];
        $this->cache[$identifier][] = $file;
        if (!is_dir($this->cachePath. '/'  . md5($identifier))) {
            mkdir($this->cachePath . '/' . md5($identifier), 0777, true);
        }
        file_put_contents($this->cachePath. '/'  . md5($identifier) . '/' . md5($file), $data);
    }
    public function __destruct()
    {
        foreach ($this->cache as $folder => $files) {
            foreach ($files as $file) {
                unlink($this->cachePath. '/'  . md5($folder) . '/' . md5($file));
            }
            rmdir($this->cachePath. '/'  . md5($folder));
        }
    }
}
