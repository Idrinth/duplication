<?php

namespace De\Idrinth\Duplication\Cache;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\FileSystem;

final class File implements Cache
{
    /**
     * @var string[][]
     */
    private array $cache = [];
    public function __construct(
        private readonly string $cachePath,
        private readonly FileSystem $fileSystem,
    ) {
    }

    public function exists(string $identifier, string $file): bool
    {
        if (isset($this->cache[$identifier]) && in_array($file, $this->cache[$identifier])) {
            return true;
        }
        return $this->fileSystem->isFile($this->cachePath. '/'  . md5($identifier) . '/' . md5($file));
    }
    public function load(string $identifier, string $file): string
    {
        return $this->fileSystem->read($this->cachePath. '/'  . md5($identifier) . '/' . md5($file));
    }
    public function save(string $identifier, string $file, string $data): void
    {
        $this->cache[$identifier] = $this->cache[$identifier] ?? [];
        $this->cache[$identifier][] = $file;
        $this->fileSystem->write($this->cachePath. '/'  . md5($identifier) . '/' . md5($file), $data);
    }
    public function __destruct()
    {
        foreach ($this->cache as $folder => $files) {
            $this->fileSystem->delete($this->cachePath. '/'  . md5($folder));
        }
    }
}
