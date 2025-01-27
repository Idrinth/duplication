<?php

namespace De\Idrinth\Duplication;

interface Encrypter
{
    public function encrypt(string $data): string;
}