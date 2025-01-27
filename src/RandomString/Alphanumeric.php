<?php

namespace De\Idrinth\Duplication\RandomString;

use De\Idrinth\Duplication\RandomString;
use InvalidArgumentException;

final readonly class Alphanumeric implements RandomString
{
    /**
     * @var string[]
     */
    private array $characters;
    private int $count;

    public function __construct()
    {
        $this->characters = str_split(
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
        );
        $this->count = count($this->characters);
    }

    public function generate(int $length): string
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Length must be a positive integer');
        }
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $this->characters[mt_rand(0, $this->count - 1)];
        }
        return $out;
    }
}