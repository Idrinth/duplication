<?php

namespace De\Idrinth\Duplication;

use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;

final class S3BucketDownloader implements Downloader
{
    private S3Client $s3;
    private FileCache $cache;
    private Encrypter $encrypter;
    private string $endpoint;
    private string $bucket;
    private string $datePrefix = '';
    private bool $encrypt;

    public function __construct(Encrypter $encrypter, FileCache $cache, string $endpoint, string $bucket, string $accessKey, string $secretAccessKey, bool $forceDatePrefix, bool $encrypt)
    {
        $this->encrypt = $encrypt;
        $this->encrypter = $encrypter;
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
        if ($forceDatePrefix) {
            $this->datePrefix = date('Y-m-d') . '/';
        }
    }

    public function get(string $path): string
    {
        if ($this->datePrefix) {
            $path = preg_replace('/^' . preg_quote($this->datePrefix, '/') . '/', '', $path);
        }
        echo "  Downloading $path.\n";
        if (!$this->cache->exists($this->endpoint, $path)) {
            $data = $this->encrypter->encrypt($this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $path])['Body'], $this->encrypt);
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
                return ltrim($data['Key'], '/');
            },
            $this->s3->listObjectsV2(['Bucket' => $this->bucket])['Contents'] ?? []
        );
        $data = array_filter($data, function ($file) {
            if (substr($file, -2) === '/.' || substr($file, -1) === '/') {
                return false;
            }
            return true;
        });
        $data = array_map(function ($path) {
            return  $this->datePrefix . $path;
        }, $data);
        echo "  Found " . count($data) . " files.\n";
        return $data;
    }
}
