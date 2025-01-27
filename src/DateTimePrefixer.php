<?php

namespace De\Idrinth\Duplication;

use DateTimeInterface;
use Stringable;

interface DateTimePrefixer extends Stringable
{
    public function __construct(DateTimeInterface $dateTime);
    public function prefix(): string;
}