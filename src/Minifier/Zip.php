<?php

namespace De\Idrinth\Duplication\Minifier;

use De\Idrinth\Duplication\FileSystem;
use De\Idrinth\Duplication\Minifier;
use ZipArchive;

final readonly class Zip implements Minifier
{
    public function __construct(private FileSystem $fileSystem)
    {
    }
    public function minify(string $content, string $fileName): string
    {
        $zip = new ZipArchive();
        $zip->open($fileName, ZipArchive::CREATE);
        $zip->addFromString($fileName, $content);
        $zip->setCompressionName($fileName, ZipArchive::CM_DEFLATE, 9);
        $zip->close();
        $zipped = $this->fileSystem->read($fileName);
        unlink($fileName);
        return $zipped;
    }
}