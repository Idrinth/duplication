<?php

namespace De\Idrinth\Duplication\Command;

use De\Idrinth\Duplication\Command;
use De\Idrinth\Duplication\Downloader;
use De\Idrinth\Duplication\DownloaderFactory;
use De\Idrinth\Duplication\Uploader;
use De\Idrinth\Duplication\UploaderFactory;
use De\Idrinth\Yaml\Yaml;

final readonly class Duplicate implements Command
{
    public function __construct(
        private string $configFile,
        private string $pidFile,
        private UploaderFactory $uploaderFactory,
        private DownloaderFactory $downloaderFactory,
    ) {
    }
    private function sync(Uploader $uploader, Downloader $downloader, string $path): void
    {
        $uploader->put($path, $downloader->get($path));
    }
    private function syncFiles(Uploader $uploader, Downloader $downloader, array $originals): void
    {
        foreach ($originals as $file) {
            $this->sync($uploader, $downloader, $file);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }
    public function run(): void
    {
        if (is_file($this->pidFile)) {
            return;
        }
        touch($this->pidFile);
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        foreach (Yaml::decodeFromFile($this->configFile) as $from) {
            $downloader = $this->downloaderFactory->getDownloader($from);
            $originals = $downloader->list();
            foreach (($from['targets'] ?? []) as $target) {
                $this->syncFiles($this->uploaderFactory->getUploader($target), $downloader, $originals);
            }
        }
        unlink($this->pidFile);
    }
}
