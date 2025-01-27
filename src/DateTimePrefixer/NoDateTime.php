<?php

namespace De\Idrinth\Duplication\DateTimePrefixer;

use DateTimeInterface;
use De\Idrinth\Duplication\DateTimePrefixer;

final readonly class NoDateTime implements DateTimePrefixer
{
    public function __toString(): string
    {
        return '';
    }

    public function __construct(DateTimeInterface $dateTime)
    {
    }

    public function prefix(): string
    {
        return '';
    }
}