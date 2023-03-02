<?php

namespace De\Idrinth\Duplication;

final class LocalDownloader implements Downloader
{
    private string $path;
    private string $prefix = '';
    private string $datePrefix = '';

    public function __construct(string $path, ?string $prefix = null, bool $forceDatePrefix=false)
    {
        $this->path = $path;
        $this->prefix = $prefix ?: basename($path);
        if ($forceDatePrefix) {
            $this->datePrefix = '/' . date('Y-m-d');
        }
    }

    public function get(string $path): string
    {
        echo "  Downloading $path.\n";
        return file_get_contents($this->path . preg_replace('/^' . preg_quote($this->prefix . $this->datePrefix, '/') . '/', '', $path)) ?: '';
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
                $output[] = $this->prefix . $this->datePrefix . '/' . ltrim(preg_replace('/^' . preg_quote($this->path, '/') . '/', '', $directory . '/' . $file), '/');
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
