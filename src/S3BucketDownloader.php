<?php

namespace De\Idrinth\S3Duplication;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;

final class S3BucketDownloader implements Downloader
{
    private S3Client $s3;
    private FileCache $cache;
    private string $endpoint;
    private string $bucket;

    public function __construct(FileCache $cache, string $endpoint, string $bucket, string $accessKey, string $secretAccessKey)
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
        $this->cache = $cache;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
    }

    public function get(string $path): string
    {
        if (!$this->cache->exists($this->endpoint, $path)) {
            $data = $this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $path])['Body'];
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
        echo "Getting objects from source {$this->endpoint}\n";
        $data = array_map(
            function (array $data) {
                return $data['Key'];
            },
            $this->s3->listObjectsV2(['Bucket' => $this->bucket])['Contents'] ?? []
        );
        echo "  Found " . count($data) . " files.\n";
        return $data;
    }
}
