<?php

namespace De\Idrinth\Duplication;

use phpseclib3\Crypt\AES;

class Encrypter
{
    private AES $key;

    public function __construct()
    {
        $this->key = new AES('ctr');
        $this->key->setIV($_ENV['ENCRYPTION_AES_IV']);
        $this->key->setKeyLength(intval($_ENV['ENCRYPTION_AES_LENGTH'], 10));
        $this->key->setKey($_ENV['ENCRYPTION_AES_IV']);
    }
    public function encrypt(string $data, bool $encrypt): string
    {
        if (!$encrypt) {
            return $data;
        }
        return $this->key->encrypt($data) ?: '';
    }
}
