<?php

namespace De\Idrinth\Duplication;

interface EncrypterFactory
{
    public function getEncrypter(bool $encrypt): Encrypter;
}