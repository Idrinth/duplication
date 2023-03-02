<?php

namespace De\Idrinth\Duplication;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey;

class Encrypter
{
    private ?PublicKey $key = null;

    public function __construct()
    {
        $file = dirname(__DIR__) . '/public.key';
        if (is_file($file) && is_readable($file)) {
            $this->key = RSA::loadPublicKey(file_get_contents($file));
        }
    }
    public function encrypt(string $data, bool $encrypt): string
    {
        if (!$encrypt || !$this->key) {
            return $data;
        }
        return $this->key->encrypt($data) ?: '';
    }
}
