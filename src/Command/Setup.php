<?php

namespace De\Idrinth\Duplication\Command;

use De\Idrinth\Duplication\RandomString;

final readonly class Setup
{
    public function __construct(
        private RandomString $randomizer,
        private string $envTarget
    ) {
    }
    public function run(): void
    {
        if (is_file($this->envTarget)) {
            return;
        }
        file_put_contents(
            $this->envTarget,
            implode("\n",
                [
                    'ENCRYPTION_AES_IV=' . $this->randomizer->generate(16),
                    'ENCRYPTION_AES_KEY=' . $this->randomizer->generate(32),
                    'ENCRYPTION_AES_LENGTH=256',
                    'LOG_FILE_LEVEL=WARNING',
                    'LOG_FILE_PATH=/var/logs/idrinth-duplication.log',
                    'LOG_CLI_LEVEL=ERROR',
                    'CACHE_PATH=/tmp',
                ],
            )
        );
    }
}