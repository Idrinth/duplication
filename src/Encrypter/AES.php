<?php

namespace De\Idrinth\Duplication\Encrypter;

use De\Idrinth\Duplication\Encrypter;
use phpseclib3\Crypt\AES as AESKey;

class AES implements Encrypter
{
    private AESKey $key;

    public function __construct(string $iv, int $keyLength, string $key)
    {
        $this->key = new AESKey('ctr');
        $this->key->setIV($iv);
        $this->key->setKeyLength($keyLength);
        $this->key->setKey($key);
    }
    public function encrypt(string $data, bool $encrypt): string
    {
        if (!$encrypt) {
            return $data;
        }
        return $this->key->encrypt($data);
    }
}
