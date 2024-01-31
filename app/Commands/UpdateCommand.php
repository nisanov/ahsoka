<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;
use LaravelZero\Framework\Components\Updater\SelfUpdateCommand;
use RuntimeException;
use function Termwind\{render};

class UpdateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $app = $this->getApplication()?->getName();

        $this->runCommand(SelfUpdateCommand::class, [], $this->output);
        $this->runCommand(MigrateCommand::class, [], $this->output);

        render(<<<HTML
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-white">$app</div>
                <span class="ml-1">
                    Self update process complete!
                </span>
            </div>
        HTML);
    }
}
