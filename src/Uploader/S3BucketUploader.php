<?php

namespace De\Idrinth\Duplication\Uploader;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use De\Idrinth\Duplication\DateTimePrefixer;
use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\Uploader;

final readonly class S3BucketUploader implements Uploader
{
    private S3Client $s3;

    public function __construct(
        private Logger $logger,
        private DateTimePrefixer $dateTimePrefixer,
        private string $bucket,
        string $endpoint,
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
            'Key' => ltrim($this->dateTimePrefixer . $path, '/'),
            'Body' => $data,
        ]);
    }
}
