<?php

namespace De\Idrinth\Duplication\Command;

use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Downloader\LocalDownloader;
use De\Idrinth\Duplication\Downloader\S3BucketDownloader;
use De\Idrinth\Duplication\Downloader\SFTPDownloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;
use De\Idrinth\Duplication\Uploader\LocalUploader;
use De\Idrinth\Duplication\Uploader\S3BucketUploader;
use De\Idrinth\Duplication\Uploader\SFTPUploader;
use De\Idrinth\Yaml\Yaml;
use InvalidArgumentException;

final readonly class Duplicate
{
    public function __construct(
        private Logger $logger,
        private Cache $cache,
        private Encrypter $encrypter,
        private string $configFile,
        private string $pidFile,
        private bool $hasMultipleDailyBackups
    ) {
    }

    private function getUploader(array $uploader): Uploader
    {
        return match ($uploader['type'] ?? '') {
            'ssh' => new SFTPUploader(
                $this->logger,
                $uploader['host'],
                $uploader['bucketPath'],
                $uploader['sshPath'],
                $uploader['port'],
                $uploader['user'],
                $uploader['privateKey'],
                $uploader['password']
            ),
            'local' => new LocalUploader(
                $this->logger,
                $uploader['path'],
                $uploader['user'],
                $uploader['group']
            ),
            'bucket' => new S3BucketUploader(
                $this->logger,
                $uploader['bucket'],
                $uploader['endpoint'],
                $uploader['access-key'],
                $uploader['secret-access-key']
            ),
            default => throw new InvalidArgumentException(
                "{$uploader['type']} is unknown and unsupported: " . json_encode($uploader)
            ),
        };
    }
    private function getDownloader(array $downloader): Downloader
    {
        return match ($downloader['type'] ?? '') {
            'ssh' => new SFTPDownloader(
                $this->logger,
                $this->encrypter,
                $this->cache,
                $this->hasMultipleDailyBackups,
                $downloader['host'],
                $downloader['bucket-path'],
                $downloader['ssh-path'],
                $downloader['port'],
                $downloader['user'],
                $downloader['private-key'],
                $downloader['password'] ?? null,
                $downloader['force-date-prefix'] ?? false,
                $downloader['encrypt-with-public-key'] ?? false
            ),
            'bucket' => new S3BucketDownloader(
                $this->logger,
                $this->encrypter,
                $this->cache,
                $this->hasMultipleDailyBackups,
                $downloader['endpoint'],
                $downloader['bucket'],
                $downloader['access-key'],
                $downloader['secret-access-key'],
                $downloader['force-date-prefix'] ?? false,
                $downloader['encrypt-with-public-key'] ?? false
            ),
            'local' => new LocalDownloader(
                $this->logger,
                $this->encrypter,
                $this->cache,
                $this->hasMultipleDailyBackups,
                $downloader['path'],
                $downloader['prefix'] ?? '',
                $downloader['force-date-prefix'] ?? false,
                $downloader['encrypt-with-public-key'] ?? false
            ),
            default => throw new InvalidArgumentException(
                "{$downloader['type']} is unknown and unsupported: " . json_encode($downloader)
            ),
        };
    }
    private function sync(Uploader $uploader, Downloader $downloader, string $path): void
    {
        $uploader->put($path, $downloader->get($path));
    }
    private function syncFiles(Uploader $uploader, Downloader $downloader, array $originals): void
    {
        foreach (array_diff($originals, $uploader->list()) as $missing) {
            $this->sync($uploader, $downloader, $missing);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }
    public function run(): void
    {
        if (is_file($this->pidFile)) {
            return;
        }
        touch($this->pidFile);
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        foreach (Yaml::decodeFromFile($this->configFile) as $from) {
            $downloader = $this->getDownloader($from);
            $originals = $downloader->list();
            foreach (($from['targets'] ?? []) as $target) {
                $this->syncFiles($this->getUploader($target), $downloader, $originals);
            }
        }
        unlink($this->pidFile);
    }
}
