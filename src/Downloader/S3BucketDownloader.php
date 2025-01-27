<?php

namespace De\Idrinth\Duplication\Downloader;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use De\Idrinth\Duplication\Cache;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\Encrypter;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Minifier;

final readonly class S3BucketDownloader implements Downloader
{
    private S3Client $s3;

    public function __construct(
        private readonly Logger $logger,
        private readonly Encrypter $encrypter,
        private readonly Cache $cache,
        private readonly Minifier $minifier,
        private readonly string $endpoint,
        private readonly string $bucket,
        string $accessKey,
        string $secretAccessKey,
    ) {
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
    }

    public function get(string $path): string
    {
        $this->logger->info("Downloading $path.");
        if (!$this->cache->exists($this->endpoint, $path)) {
            $data = $this->encrypter->encrypt(
                $this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $path])['Body'],
            );
            $this->cache->save($this->endpoint, $path, $this->minifier->minify($data));
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
        $this->logger->info("Found " . count($data) . " files.");
        return $data;
    }
}
