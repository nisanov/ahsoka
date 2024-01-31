<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use function Termwind\{render};

class InitiateCommand extends Ahsoka
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:initiate';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install the application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $app = $this->getApplicationName();
        $database = Config::get('database.connections.sqlite.database');
        if (empty($database)) {
            throw new RuntimeException("The sqlite database path must be configured.");
        }

        $status = 'already exists';
        if (!file_exists($database)) {
            $status = 'created';
            $directory = dirname($database);
            if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
                throw new RuntimeException("Directory [$directory] was not created.");
            }
            if (!touch($database)) {
                throw new RuntimeException("Failed to create the database: $database");
            }
        }

        render(<<<HTML
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-white">$app</div>
                <span class="ml-1">
                    Database $status: <em>$database</em>.
                </span>
            </div>
        HTML);

        $this->runCommand(MigrateCommand::class, [], $this->output);

        render(<<<HTML
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-white">$app</div>
                <span class="ml-1">
                    Installation process complete!
                </span>
            </div>
        HTML);
    }
}
