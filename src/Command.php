<?php

namespace De\Idrinth\S3Duplication;

use De\Idrinth\Yaml\Yaml;
use InvalidArgumentException;

final class Command
{
    private function getUploader(array $uploader): Uploader
    {
        switch ($uploader['type'] ?? 'bucket') {
            case 'ssh':
                return new SFTPUploader($uploader['host'], $uploader['bucketPath'], $uploader['sshPath'], $uploader['port'], $uploader['user'], $uploader['privateKey'], $uploader['password']);
            case 'local':
                return new LocalUploader($uploader['path'], $uploader['user'], $uploader['group']);
            case 'bucket':
                return new S3BucketUploader($uploader['bucket'], $uploader['endpoint'], $uploader['access-key'], $uploader['secret-access-key']);
            default:
                throw InvalidArgumentException("{$uploader['type']} is unknown and unsupported.");
        }
    }
    private function getDownloader(array $downloader, FileCache $cache): Downloader
    {
        switch ($downloader['case'] ?? 'bucket') {
            case 'ssh':
                return new SFTPDownloader($cache, $downloader['host'], $downloader['bucket-path'], $downloader['ssh-path'], $downloader['port'], $downloader['user'], $downloader['private-key'], $downloader['password']);
            case 'bucket':
                return new S3BucketDownloader($cache, $downloader['endpoint'], $downloader['bucket'], $downloader['access-key'], $downloader['secret-access-key']);
            case 'local':
                return new LocalDownloader($downloader['path']);
            default:
                throw InvalidArgumentException("{$downloader['type']} is unknown and unsupported.");
        }
    }
    public function run ()
    {
        $cache = new FileCache();
        foreach (Yaml::decodeFromFile(__DIR__ . '/../config.yml') as $from) {
            $downloader = $this->getDownloader($from, $cache);
            $originals = $downloader->list();
            foreach (($from['targets'] ?? []) as $target) {
                $uploader = $this->getUploader($target);
                foreach (array_diff($originals, $uploader->list()) as $missing) {
                    $uploader->put($missing, $downloader->get($missing));
                }
            }
        }
    }
}
