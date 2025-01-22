<?php

namespace De\Idrinth\Duplication\Uploader;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;

final readonly class S3BucketUploader implements Uploader
{
    private S3Client $s3;

    public function __construct(
        private Logger $logger,
        private string $bucket,
        private string $endpoint,
        string $accessKey,
        string $secretAccessKey
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

    public function put(string $path, string $data): void
    {
       $this->logger->info("Uploading $path.");
       $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => ltrim($path, '/'),
            'Body' => $data,
        ]);
    }

    public function list(): array
    {
        $this->logger->info("Getting objects from target $this->endpoint");
        $data = array_map(
            function (array $data) {
                return ltrim($data['Key'], '/');
            },
            $this->s3->listObjectsV2(['Bucket' => $this->bucket])['Contents'] ?? []
        );
        $data = array_filter($data, function ($file) {
            return !str_ends_with($file, '/.') && !str_ends_with($file, '/');
        });
        $this->logger->info("Found " . count($data) . " objects.");
        return $data;
    }
}
