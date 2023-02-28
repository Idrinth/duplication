<?php

namespace De\Idrinth\S3Duplication;

use De\Idrinth\Yaml\Yaml;

final class Command
{
    public function run ()
    {
        $cache = new FileCache();
        foreach (Yaml::decodeFromFile(__DIR__ . '/../config.yml') as $from) {
            $downloader = ($from['type']??'bucket') === 'bucket'
                ? new S3BucketDownloader($cache, $from['endpoint'], $from['access-key'], $from['secret-access-key'])
                : new SFTSDownloader($cache, $from['host'], $from['bucket-path'], $from['ssh-path'], $from['port'], $from['user'], $from['private-key'], $from['password']);
            $originals = $downloader->list();
            foreach ($from['targets'] as $target) {
                $uploader = new S3BucketUploader($target['bucket'], $target['endpoint'], $target['access-key'], $target['secret-access-key']);
                foreach (array_diff($originals, $uploader->list()) as $missing) {
                    $uploader->put($missing, $downloader->get($missing));
                }
            }
        }
    }
}
