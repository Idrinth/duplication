<?php

namespace De\Idrinth\Duplication;

interface UploaderFactory
{
    public function getUploader(array $config): Uploader;
}