<?php

namespace De\Idrinth\Duplication\Minifier;

use De\Idrinth\Duplication\Minifier;

class NoMinifier implements Minifier
{
    public function minify(string $content, string $fileName): string
    {
        return $content;
    }
}