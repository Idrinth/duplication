<?php

namespace De\Idrinth\Duplication;

interface DateTimePrefixerFactory
{
    public function getDateTimePrefixer(bool $prefix): DateTimePrefixer;
}