<?php

namespace De\Idrinth\Duplication\Uploader;

use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final class SFTPUploader implements Uploader
{
    /**
     * @var string[]
     */
    private array $mapping = [];
    private readonly SFTP $sftp;

    public function __construct(
        private readonly Logger $logger,
        private readonly string $host,
        private readonly string $bucketPath,
        private readonly string $sshPath,
        int $port,
        string $user,
        string $privateKey,
        ?string $password = null
    ) {
        $this->sftp = new SFTP($host, $port);
        $this->sftp->login(
            $user,
            RSA::loadPrivateKey(file_get_contents($privateKey), $password ?? '')
        );
    }

    public function put(string $path, string $data): void
    {
        $this->logger->info("Uploading $path.");
        $file = $this->mapping[$path];
        $this->sftp->mkdir(dirname($file), -1, true);
        $this->sftp->put($file, $data);
    }

    public function list(): array
    {
        $this->mapping = [];
        $this->logger->info("Getting objects from source $this->host");
        foreach (array_filter($this->sftp->nlist($this->sshPath, true), function ($file) {
            return $file !== '.' && $file !== '..' && !str_ends_with($file, '/.') && !str_ends_with($file, '/..');
        }) as $file) {
            $this->mapping[$this->bucketPath . '/' . $file] = $this->sshPath . '/' . $file;
        }
        $this->logger->info("Found " . count($this->mapping) . " files.");
        return array_keys($this->mapping);
    }
}
