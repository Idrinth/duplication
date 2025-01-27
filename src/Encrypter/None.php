<?php

namespace De\Idrinth\Duplication\Encrypter;

use De\Idrinth\Duplication\Encrypter;

final readonly class None implements Encrypter
{
    public function encrypt(string $data): string
    {
        return $data;
    }
}