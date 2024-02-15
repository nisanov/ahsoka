<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use UnexpectedValueException;

class MaintenanceDatabaseCommand extends Ahsoka
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'maintenance:database';

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

        $this->output->write("$app Checking database: ");

        $database = Config::get('database.connections.sqlite.database');
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
