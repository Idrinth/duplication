<?php

namespace De\Idrinth\Duplication;

interface RandomString
{
    public function generate(int $length): string;
}