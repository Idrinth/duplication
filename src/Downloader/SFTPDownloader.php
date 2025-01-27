<?php

namespace De\Idrinth\Duplication\Downloader;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Minifier;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final readonly class SFTPDownloader implements Downloader
{
    private SFTP $sftp;

    public function __construct(
        private Logger $logger,
        private Encrypter $encrypter,
        private Cache $cache,
        private Minifier $minifier,
        private string $host,
        private string $sshPath,
        int $port,
        string $user,
        string $privateKey,
        ?string $password,
    ) {
        $this->sftp = new SFTP($host, $port);
        $this->sftp->login(
            $user,
            RSA::loadPrivateKey(file_get_contents($privateKey), $password ?? '')
        );
    }

    public function get(string $path): string
    {
        $this->logger->info("Downloading $path.");
        if (!$this->cache->exists($this->host, $path)) {
            $data = $this->encrypter->encrypt($this->sftp->get($path));
            $this->cache->save($this->host, $path, $this->minifier->minify($data, basename($path)));
            return $data;
        }
        return $this->cache->load($this->host, $path);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        $this->logger->info("Getting objects from source $this->host.");
        $files = array_filter($this->sftp->nlist($this->sshPath, true), function ($file) {
            return $file !== '.' && $file !== '..' && !str_ends_with($file, '/.') && !str_ends_with($file, '/..');
        });
        $this->logger->info("Found " . count($files) . " files.");
        return $files;
    }
}
