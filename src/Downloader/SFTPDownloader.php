<?php

namespace De\Idrinth\Duplication\Downloader;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final class SFTPDownloader implements Downloader
{
    /**
     * @var string[]
     */
    private array $mapping = [];
    private SFTP $sftp;
    private string $datePrefix = '';

    public function __construct(
        private readonly Logger $logger,
        private readonly Encrypter $encrypter,
        private readonly Cache $cache,
        bool $hasMultipleDailyBackups,
        private readonly string $host,
        private readonly string $bucketPath,
        private readonly string $sshPath,
        int $port,
        string $user,
        string $privateKey,
        ?string $password,
        bool $forceDatePrefix,
        private readonly bool $encrypt
    ) {
        $this->sftp = new SFTP($host, $port);
        $this->sftp->login(
            $user,
            RSA::loadPrivateKey(file_get_contents($privateKey), $password ?? '')
        );
        if ($forceDatePrefix) {
            $this->datePrefix = date('Y-m-d') . ($hasMultipleDailyBackups ? date('-H') : '') . '/';
        }
    }

    public function get(string $path): string
    {
        $file = $this->mapping[$path];
        $this->logger->info("Downloading $file.");
        if (!$this->cache->exists($this->host, $file)) {
            $data = $this->encrypter->encrypt($this->sftp->get($file), $this->encrypt);
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
        $this->logger->info("Getting objects from source $this->host.");
        foreach (array_filter($this->sftp->nlist($this->sshPath, true), function ($file) {
            return $file !== '.' && $file !== '..' && !str_ends_with($file, '/.') && !str_ends_with($file, '/..');
        }) as $file) {
            $this->mapping[$this->bucketPath . '/' . $this->datePrefix . $file] = $this->sshPath . '/' . $file;
        }
        $this->logger->info("Found " . count($this->mapping) . " files.");
        return array_keys($this->mapping);
    }
}
