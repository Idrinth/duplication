<?php

namespace De\Idrinth\S3Duplication;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;

final class S3BucketUploader implements Uploader
{
    private S3Client $s3;
    private string $endpoint;
    private string $bucket;

    public function __construct(string $bucket, string $endpoint, string $accessKey, string $secretAccessKey)
    {
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
        $this->bucket = $bucket;
        $this->endpoint = $endpoint;
    }

    public function put(string $path, string $data): void
    {
       $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => ltrim($path, '/'),
            'Body' => $data,
        ]);
    }

    public function list(): array
    {
        echo "  Getting objects from target {$this->endpoint}\n";
        $data = array_map(
            function (array $data) {
                return $data['Key'];
            },
            $this->s3->listObjectsV2(['Bucket' => $this->bucket])['Contents'] ?? []
        );
        echo "    Found " . count($data) . " objects.\n";
        return $data;
    }
}
