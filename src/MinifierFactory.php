<?php

namespace De\Idrinth\Duplication;

interface MinifierFactory
{
    public function getMinifier(bool $minify): Minifier;
}