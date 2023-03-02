<?php

namespace De\Idrinth\Duplication;

use De\Idrinth\Yaml\Yaml;
use InvalidArgumentException;

final class Command
{
    private function getUploader(array $uploader): Uploader
    {
        switch ($uploader['type'] ?? '') {
            case 'ssh':
                return new SFTPUploader($uploader['host'], $uploader['bucketPath'], $uploader['sshPath'], $uploader['port'], $uploader['user'], $uploader['privateKey'], $uploader['password']);
            case 'local':
                return new LocalUploader($uploader['path'], $uploader['user'], $uploader['group']);
            case 'bucket':
                return new S3BucketUploader($uploader['bucket'], $uploader['endpoint'], $uploader['access-key'], $uploader['secret-access-key']);
            default:
                throw InvalidArgumentException("{$uploader['type']} is unknown and unsupported: " . json_encode($uploader));
        }
    }
    private function getDownloader(array $downloader, FileCache $cache): Downloader
    {
        switch ($downloader['type'] ?? '') {
            case 'ssh':
                return new SFTPDownloader($cache, $downloader['host'], $downloader['bucket-path'], $downloader['ssh-path'], $downloader['port'], $downloader['user'], $downloader['private-key'], $downloader['password'], $downloader['force-date-prefix'] ?? false);
            case 'bucket':
                return new S3BucketDownloader($cache, $downloader['endpoint'], $downloader['bucket'], $downloader['access-key'], $downloader['secret-access-key'], $downloader['force-date-prefix'] ?? false);
            case 'local':
                return new LocalDownloader($downloader['path'], $downloader['prefix'] ?? null, $downloader['force-date-prefix'] ?? false);
            default:
                throw InvalidArgumentException("{$downloader['type']} is unknown and unsupported: " . json_encode($downloader));
        }
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
    public function run ()
    {
        $pid = dirname(__DIR__) . '/running';
        if (is_file($pid)) {
            return;
        }
        touch($pid);
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        $cache = new FileCache();
        foreach (Yaml::decodeFromFile(__DIR__ . '/../config.yml') as $from) {
            $downloader = $this->getDownloader($from, $cache);
            $originals = $downloader->list();
            foreach (($from['targets'] ?? []) as $target) {
                $this->syncFiles($this->getUploader($target), $downloader, $originals);
            }
        }
        unlink($pid);
    }
}
