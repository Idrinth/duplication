<?php

namespace De\Idrinth\Duplication\Uploader;

use De\Idrinth\Duplication\DateTimePrefixer;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP;

final readonly class SFTPUploader implements Uploader
{
    private SFTP $sftp;

    public function __construct(
        private Logger $logger,
        private DateTimePrefixer $dateTimePrefixer,
        string $host,
        private string $prefix,
        private string $sshPath,
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
        $file = $this->sshPath . $this->prefix . $this->dateTimePrefixer . $path;
        $this->sftp->mkdir(dirname($file), -1, true);
        $this->sftp->put($file, $data);
    }
}
