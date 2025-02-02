<?php

namespace De\Idrinth\Duplication\Factories;

use DateTimeImmutable;
use De\Idrinth\Duplication\Cache\File;
use De\Idrinth\Duplication\Command;
use De\Idrinth\Duplication\Command\Duplicate;
use De\Idrinth\Duplication\Command\Setup;
use De\Idrinth\Duplication\FileSystem\LocalFileSystem;
use De\Idrinth\Duplication\Logger\CLILogger;
use De\Idrinth\Duplication\Logger\FileLogger;
use De\Idrinth\Duplication\Logger\MultiLogger;
use De\Idrinth\Duplication\LogLevel;
use De\Idrinth\Duplication\RandomString\Alphanumeric;
use Dotenv\Dotenv;
use InvalidArgumentException;

final readonly class CommandFactory
{
    public function __construct(
        private array $env,
        private string $rootDir
    ) {
    }
    public static function prepare($rootDir): CommandFactory
    {
        ini_set('memory_limit', -1);
        Dotenv::createImmutable($rootDir)
            ->safeLoad();
        return (new self($_ENV, $rootDir));
    }
    public function create(string $command): Command
    {
        return match ($command) {
            'setup' => $this->setup(),
            'duplicate' => $this->duplicate(),
            default => throw new InvalidArgumentException("Unknown command '$command'"),
        };
    }
    private function setup(): Setup
    {
        return new Setup(
            logger: new CLILogger(logLevel: LogLevel::INFO),
            randomizer: new Alphanumeric(),
            envTarget: $this->rootDir . '/.env'
        );
    }
    private function duplicate(): Duplicate
    {
        $logLevelFactory = new LogLevelFactory();
        $factory = new Factory(
            logger: new MultiLogger(
                new FileLogger(
                    $logLevelFactory->convert($this->env['LOG_FILE_LEVEL']),
                    $this->env['LOG_FILE_PATH'],
                ),
                new CLILogger(
                    $logLevelFactory->convert($this->env['LOG_CLI_LEVEL']),
                ),
            ),
            cache: new File($this->env['CACHE_PATH'], new LocalFileSystem()),
            now: new DateTimeImmutable(),
            hasMultipleDailyBackups: strtolower($this->env['MULTIPLE_DAILY_BACKUPS']) === 'true',
            aesIv: $this->env['ENCRYPTION_AES_IV'],
            aesKey: $this->env['ENCRYPTION_AES_KEY'],
            aesLength: intval($this->env['ENCRYPTION_AES_LENGTH']),
        );

        return new Duplicate(
            configFile: $this->rootDir . '/config.yml',
            pidFile: $this->rootDir . '/is-running',
            uploaderFactory: $factory,
            downloaderFactory: $factory,
        );
    }
}