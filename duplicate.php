<?php

use Aws\AwsClient;
use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use De\Idrinth\Yaml\Yaml;

require_once ('vendor/autoload.php');

class BackUp {
    /**
     * @var AwsClient[]
     */
    private array $s3s = [];
    /**
     * @var string[]
     */
    private array $cache = [];
    private function build(array $connection): S3Client
    {
        return new S3Client([
            'service' => 's3',
            'region' => 'other',
            'version' => 'latest',
            'endpoint' => 'https://' . $connection['endpoint'],
            'disable_host_prefix_injection' => true,
            'bucket_endpoint' => true,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $connection['access-key'],
                'secret' => $connection['secret-access-key'],
            ],
            'http'  => [
                'verify' => CaBundle::getBundledCaBundlePath(),
            ],
        ]);
    }
    private function get($path, $endpoint, $bucket)
    {
        if (!isset($this->cache[$endpoint])) {
            $this->cache[$endpoint] = [];
            if (!is_dir(__DIR__ . '/' . md5($endpoint))) {
                mkdir (__DIR__ . '/' . md5($endpoint), 0777, true);
            }
        }
        if (!in_array($path, $this->cache[$endpoint])) {
            $this->cache[$endpoint][] = $path;
            $data = $this->s3s[$endpoint]->getObject(['Bucket' => $bucket, 'Key' => $path])['Body'];;
            file_put_contents(__DIR__ . '/' . md5($endpoint) . '/' .md5($path), $data);
            return $data;
        }
        return file_get_contents(__DIR__ . '/' . md5($endpoint) . '/' .md5($path));
    }
    private function getObjectKeys(string $bucket, string $endpoint)
    {
        return array_map(
            function (array $data) {
                return $data['Key'];
            },
            $this->s3s[$endpoint]->listObjectsV2(['Bucket' => $bucket])['Contents'] ?? []
        );
    }
    public function run ()
    {
        foreach (Yaml::decodeFromFile(__DIR__ . '/config.yml') as $from) {
            $this->s3s[$from['endpoint']] = $this->s3s[$from['endpoint']] ?? $this->build($from);
            $originals = $this->getObjectKeys($from['bucket'], $from['endpoint']);
            var_dump($originals);
            foreach ($from['targets'] as $target) {
                $this->s3s[$target['endpoint']] = $this->s3s[$target['endpoint']] ?? $this->build($target);
                $existing = $this->getObjectKeys($target['bucket'], $target['endpoint']);
                var_dump($existing);
                foreach (array_diff($originals, $existing) as $missing) {
                    $this->s3s[$target['endpoint']]->putObject([
                        'Bucket' => $target['bucket'],
                        'Key' => $missing,
                        'Body' => $this->get($missing, $from['endpoint'], $from['bucket']),
                    ]);
                }
            }
        }
    }
    public function __destruct() {
        foreach ($this->cache as $folder => $files) {
            foreach ($files as $file) {
                unlink(__DIR__ . '/' . md5($folder) . '/' . md5($file));
            }
            rmdir(__DIR__ . '/' . md5($folder));
        }
    }
}

(new Backup())->run();