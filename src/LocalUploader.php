<?php

namespace De\Idrinth\S3Duplication;

final class LocalUploader implements Uploader
{
    private string $path;
    private string $user;
    private string $group;
    
    public function __construct(string $path, string $user, string $group)
    {
        $this->path = $path;
        $this->user = $user;
        $this->group = $group;
        mkdir($this->path, 0777, true);
    }

    public function put(string $path, string $data): void
    {
        if (!$data) {
            return;
        }
        $file = $this->path . '/' . $path;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        file_put_contents($file, $data);
        chgrp($file, $this->group);
        chown($file, $this->user);
    }

    private function scan(string $directory, array &$output): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (array_diff(scandir($directory), ['.', '..']) as $file) {
            if (is_dir($directory . '/' . $file)) {
                $this->scan($directory . '/' . $file, $output);
            } else {
                $output[] = preg_replace('/^' . preg_quote($this->path, '/') . '/', '', $directory . '/' . $file);
            }
        }
    }

    public function list(): array
    {
        echo "  Getting objects from target {$this->path}\n";
        $output = [];
        $this->scan($this->path, $output);
        echo "    Found " . count($output) . " objects.\n";
        return $output;
    }
}
