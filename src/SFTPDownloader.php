<?php

namespace De\Idrinth\Duplication;

use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final class SFTPDownloader implements Downloader
{
    /**
     * @var string[]
     */
    private array $mapping = [];
    private FileCache $cache;
    private string $host;
    private string $bucketPath;
    private string $sshPath;
    private SFTP $sftp;
    private string $datePrefix = '';

    public function __construct(FileCache $cache, string $host, string $bucketPath, string $sshPath, int $port, string $user, string $privateKey, ?string $password = null, bool $forceDatePrefix = false)
    {
        $this->cache = $cache;
        $this->host = $host;
        $this->bucketPath = $bucketPath;
        $this->sshPath = $sshPath;
        $this->sftp = new SFTP($host, $port);
        if ($password === null) {
            $this->sftp->login(
                $user,
                RSA::loadPrivateKey(file_get_contents($privateKey))
            );
            return;
        }
        $this->sftp->login(
            $user,
            RSA::loadPrivateKey(file_get_contents($privateKey), $password)
        );
        if ($forceDatePrefix) {
            $this->datePrefix = date('Y-m-d') . '/';
        }
    }

    public function get(string $path): string
    {
        $file = $this->mapping[$path];
        echo "  Downloading $file.\n";
        if (!$this->cache->exists($this->host, $file)) {
            $data = $this->sftp->get($file);
            $this->cache->save($this->host, $file, $data);
            return $data;
        }
        return $this->cache->load($this->host, $file);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        $this->mapping = [];
        echo "Getting objects from source {$this->host}\n";
        foreach (array_filter($this->sftp->nlist($this->sshPath, true), function ($file) {
            return $file !== '.' && $file !== '..' && substr($file, -2) !== '/.' && substr($file, -3) !== '/..';
        }) as $file) {
            $this->mapping[$this->bucketPath . '/' . $this->datePrefix . $file] = $this->sshPath . '/' . $file;
        }
        echo "  Found " . count($this->mapping) . " files.\n";
        return array_keys($this->mapping);
    }
}
