<?php

namespace De\Idrinth\Duplication\DateTimePrefixer;

use DateTimeInterface;
use De\Idrinth\Duplication\DateTimePrefixer;

final readonly class DateHour implements DateTimePrefixer
{
    private string $output;

    public function __toString(): string
    {
        return $this->output . DIRECTORY_SEPARATOR;
    }

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->output = $dateTime->format('Y-m-d-H');
    }

    public function prefix(): string
    {
        return $this->output;
    }
}