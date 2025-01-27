<?php

namespace De\Idrinth\Duplication;

interface Minifier
{
    public function minify(string $content, string $fileName): string;
}