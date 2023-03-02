<?php

namespace De\Idrinth\Duplication;

class Encrypter
{
    private string $key;

    public function __construct()
    {
        $file = dirname(__DIR__) . '/public.key';
        if (is_file($file) && is_readable($file)) {
            $this->key = substr(file_get_contents($file) ?: '', 8) ?: '';
        }
    }
    public function encrypt(string $data, bool $encrypt): string
    {
        if (!$encrypt || !$this->key) {
            return $data;
        }
        return openssl_encrypt(
            $data,
            $_ENV['SSL_ALGORYTHM'],
            $this->key,
            OPENSSL_RAW_DATA,
            $_ENV['SSL_INITILIZATION_VECTOR']
        );
    }
}
