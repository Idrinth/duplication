<?php

namespace De\Idrinth\Duplication\Downloader;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;

final class S3BucketDownloader implements Downloader
{
    private S3Client $s3;
    private string $datePrefix = '';
    private bool $encrypt;

    public function __construct(
        private readonly Logger $logger,
        private readonly Encrypter $encrypter,
        private readonly Cache $cache,
        bool $hasMultipleDailyBackups,
        private readonly string $endpoint,
        private readonly string $bucket,
        string $accessKey,
        string $secretAccessKey,
        bool $forceDatePrefix,
        bool $encrypt
    ) {
        $this->encrypt = $encrypt;
        $this->s3 = new S3Client([
            'service' => 's3',
            'region' => 'other',
            'version' => 'latest',
            'endpoint' => 'https://' . $endpoint,
            'disable_host_prefix_injection' => true,
            'bucket_endpoint' => true,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretAccessKey,
            ],
            'http'  => [
                'verify' => CaBundle::getBundledCaBundlePath(),
            ],
        ]);
        if ($forceDatePrefix) {
            $this->datePrefix = date('Y-m-d') . ($hasMultipleDailyBackups ? date('-H') : '') . '/';
        }
    }

    public function get(string $path): string
    {
        if ($this->datePrefix) {
            $path = preg_replace('/^' . preg_quote($this->datePrefix, '/') . '/', '', $path);
        }
        $this->logger->info("Downloading $path.");
        if (!$this->cache->exists($this->endpoint, $path)) {
            $data = $this->encrypter->encrypt(
                $this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $path])['Body'],
                $this->encrypt
            );
            $this->cache->save($this->endpoint, $path, $data);
            return $data;
        }
        return $this->cache->load($this->endpoint, $path);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        $this->logger->info("Getting objects from source $this->endpoint");
        $data = array_map(
            function (array $data) {
                return ltrim($data['Key'], '/');
            },
            $this->s3->listObjectsV2(['Bucket' => $this->bucket])['Contents'] ?? []
        );
        $data = array_filter($data, function ($file) {
            return !str_ends_with($file, '/.') && !str_ends_with($file, '/');
        });
        $data = array_map(function ($path) {
            return  $this->datePrefix . $path;
        }, $data);
        $this->logger->info("Found " . count($data) . " files.");
        return $data;
    }
}
