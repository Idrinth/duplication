<?php

namespace De\Idrinth\Duplication\Factories;

use DateTimeInterface;
use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\DateTimePrefixer;
use De\Idrinth\Duplication\DateTimePrefixer\Date;
use De\Idrinth\Duplication\DateTimePrefixer\DateHour;
use De\Idrinth\Duplication\DateTimePrefixer\NoDateTime;
use De\Idrinth\Duplication\DateTimePrefixerFactory;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Downloader\LocalDownloader;
use De\Idrinth\Duplication\Downloader\S3BucketDownloader;
use De\Idrinth\Duplication\Downloader\SFTPDownloader;
use De\Idrinth\Duplication\DownloaderFactory;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Encrypter\AES;
use De\Idrinth\Duplication\Encrypter\None;
use De\Idrinth\Duplication\EncrypterFactory;
use De\Idrinth\Duplication\FileSystem\LocalFileSystem;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Minifier;
use De\Idrinth\Duplication\Minifier\NoMinifier;
use De\Idrinth\Duplication\Minifier\Zip;
use De\Idrinth\Duplication\MinifierFactory;
use De\Idrinth\Duplication\Uploader;
use De\Idrinth\Duplication\Uploader\LocalUploader;
use De\Idrinth\Duplication\Uploader\S3BucketUploader;
use De\Idrinth\Duplication\Uploader\SFTPUploader;
use De\Idrinth\Duplication\UploaderFactory;
use InvalidArgumentException;

final readonly class Factory implements MinifierFactory, EncrypterFactory, DownloaderFactory, UploaderFactory, DateTimePrefixerFactory
{
    public function __construct(
        private Logger $logger,
        private Cache $cache,
        private DateTimeInterface $now,
        private bool $hasMultipleDailyBackups,
        private string $aesIv,
        private string $aesKey,
        private int $aesLength
    ) {
    }
    public function getEncrypter(bool $encrypt): Encrypter
    {
        return $encrypt ? new AES($this->aesIv, $this->aesLength, $this->aesKey) : new None();
    }

    public function getDownloader(array $config): Downloader
    {
        return match ($config['type'] ?? '') {
            'ssh' => new SFTPDownloader(
                $this->logger,
                $this->getEncrypter($config['encrypt-with-public-key'] ?? false),
                $this->cache,
                $this->getMinifier($config['minify'] ?? false),
                $config['host'],
                $config['ssh-path'],
                $config['port'],
                $config['user'],
                $config['private-key'],
                $config['password'] ?? null
            ),
            'bucket' => new S3BucketDownloader(
                $this->logger,
                $this->getEncrypter($config['encrypt-with-public-key'] ?? false),
                $this->cache,
                $this->getMinifier($config['minify'] ?? false),
                $config['endpoint'],
                $config['bucket'],
                $config['access-key'],
                $config['secret-access-key'],
            ),
            'local' => new LocalDownloader(
                $this->logger,
                $this->getEncrypter($downloader['encrypt-with-public-key'] ?? false),
                $this->cache,
                new LocalFileSystem(),
                $this->getMinifier($config['minify'] ?? false),
                $config['path'],
            ),
            default => throw new InvalidArgumentException(
                "{$config['type']} is unknown and unsupported: " . json_encode($config)
            ),
        };
    }

    public function getUploader(array $config): Uploader
    {
        return match ($config['type'] ?? '') {
            'ssh' => new SFTPUploader(
                $this->logger,
                $this->getDateTimePrefixer($config['force-data-prefix'] ?? false),
                $config['host'],
                $config['prefix'],
                $config['sshPath'],
                $config['port'],
                $config['user'],
                $config['privateKey'],
                $config['password']
            ),
            'local' => new LocalUploader(
                $this->logger,
                $this->getDateTimePrefixer($config['force-data-prefix'] ?? false),
                new LocalFileSystem(),
                $config['path'],
                $config['user'],
                $config['group']
            ),
            'bucket' => new S3BucketUploader(
                $this->logger,
                $this->getDateTimePrefixer($config['force-data-prefix'] ?? false),
                $config['bucket'],
                $config['endpoint'],
                $config['access-key'],
                $config['secret-access-key']
            ),
            default => throw new InvalidArgumentException(
                "{$config['type']} is unknown and unsupported: " . json_encode($config)
            ),
        };
    }

    public function getDateTimePrefixer(bool $prefix): DateTimePrefixer
    {
        if (! $prefix) {
            return new NoDateTime($this->now);
        }
        return $this->hasMultipleDailyBackups
            ? new DateHour($this->now)
            : new Date($this->now);
    }

    public function getMinifier(bool $minify): Minifier
    {
        if (! $minify) {
            return new NoMinifier();
        }
        if (!extension_loaded('zip')) {
            return new NoMinifier();
        }
        return new Zip(new LocalFileSystem());
    }
}