<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Symfony\Component\Console\Command\Command;
use UnexpectedValueException;

class SystemInstallCommand extends Ahsoka
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'system:install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $app = $this->getApplicationName();

        $this->output->write("$app Checking font: ");

        $path = config('font.path');
        if (empty($path)) {
            throw new UnexpectedValueException("The font path must be configured");
        }

        if (!file_exists($path)) {
            $this->output->writeln("<comment>✘</comment> <comment>does not exist</comment>");
            $this->output->write("$app Retrieving font: ");
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
                throw new UnexpectedValueException("Failed to create the directory: $directory");
            }
            $source = config('font.source');
            if (empty($source)) {
                throw new UnexpectedValueException("The font source must be configured");
            }
            $font = file_get_contents($source);
            if (empty($font)) {
                throw new UnexpectedValueException("Failed to retrieve the font source: $source");
            }
            if (file_put_contents($path, $font) === false) {
                throw new UnexpectedValueException("Failed to store the font: $path");
            }
        }

        $this->output->writeln("<info>✔</info> <comment>$path</comment>");

        $this->output->write("$app Checking database: ");

        $database = config('database.connections.sqlite.database');
        if (empty($database)) {
            throw new UnexpectedValueException("The sqlite database path must be configured");
        }

        if (!file_exists($database)) {
            $this->output->writeln("<comment>✘</comment> <comment>does not exist</comment>");
            $this->output->write("$app Creating database: ");
            $directory = dirname($database);
            if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
                throw new UnexpectedValueException("Failed to create the directory: $directory");
            }
            if (!touch($database)) {
                throw new UnexpectedValueException("Failed to create the database: $database");
            }
        }

        $this->output->writeln("<info>✔</info> <comment>$database</comment>");

        $this->call(MigrateCommand::class, ['--force' => null]);

        return Command::SUCCESS;
    }
}
