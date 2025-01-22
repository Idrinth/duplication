<?php

namespace De\Idrinth\Duplication\Command;

use De\Idrinth\Duplication\Logger;
use De\Idrinth\Duplication\RandomString;

final readonly class Setup
{
    public function __construct(
        private Logger $logger,
        private RandomString $randomizer,
        private string $envTarget
    ) {
    }
    public function run(): void
    {
        if (is_file($this->envTarget)) {
            return;
        }
        $iv = $this->randomizer->generate(16);
        $key = $this->randomizer->generate(32);
        file_put_contents(
            $this->envTarget,
            implode(
                "\n",
                [
                    'ENCRYPTION_AES_IV=' . $iv,
                    'ENCRYPTION_AES_KEY=' . $key,
                    'ENCRYPTION_AES_LENGTH=256',
                    'LOG_FILE_LEVEL=WARNING',
                    'LOG_FILE_PATH=/var/logs/idrinth-duplication.log',
                    'LOG_CLI_LEVEL=ERROR',
                    'CACHE_PATH=/tmp',
                    'DAYS_TO_STORE=7',
                    'MULTIPLE_DAILY_BACKUPS=false'
                ],
            )
        );
        $this->logger->info("Generated AES IV, please safe it: $iv");
        $this->logger->info("Generated AES KEY, please safe it: $key");
    }
}