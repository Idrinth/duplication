<?php

namespace De\Idrinth\Duplication;

use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final class SFTPUploader implements Uploader
{
    /**
     * @var string[]
     */
    private array $mapping = [];
    private string $host;
    private string $bucketPath;
    private string $sshPath;
    private SFTP $sftp;

    public function __construct(string $host, string $bucketPath, string $sshPath, int $port, string $user, string $privateKey, ?string $password = null)
    {
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
    }

    public function put(string $path, string $data): string
    {
        $file = $this->mapping[$path];
        $this->sftp->mkdir(dirname($file), -1, true);
        $this->sftp->put($file, $data);
    }

    public function list(): array
    {
        $this->mapping = [];
        echo "Getting objects from source {$this->host}\n";
        foreach (array_filter($this->sftp->nlist($this->sshPath, true), function ($file) {
            return $file !== '.' && $file !== '..' && substr($file, -2) !== '/.' && substr($file, -3) !== '/..';
        }) as $file) {
            $this->mapping[$this->bucketPath . '/' . $file] = $this->sshPath . '/' . $file;
        }
        echo "Found " . count($this->mapping) . " files.\n";
        return array_keys($this->mapping);
    }
}
