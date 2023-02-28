<?php

namespace De\Idrinth\S3Duplication;

final class LocalDownloader implements Downloader
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(string $path): string
    {
        return file_get_contents($this->path . $path) ?: '';
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
        echo "Getting objects from source {$this->path}\n";
        $output = [];
        $this->scan($this->path, $output);
        echo "  Found " . count($output) . " objects.\n";
        return $output;
    }
}
