<?php

declare(strict_types=1);

namespace App\Commands;

use Humbug\SelfUpdate\Updater;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Symfony\Component\Console\Command\Command;

class SystemUpdateCommand extends Ahsoka
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'system:update';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the application';

    /**
     * Execute the console command.
     *
     * @param Updater $updater
     * @return int
     */
    public function handle(Updater $updater): int
    {
        $app = $this->getApplicationName();

        $this->output->write("$app Updating the application: ");

        $result = $updater->update();
        if ($result) {
            $this->output->writeln("<info>✔</info> <comment>Updated from version {$updater->getOldVersion()} to {$updater->getNewVersion()}</comment>");
            $this->call(MigrateCommand::class, ['--force' => null]);
        } elseif (! $updater->getNewVersion()) {
            $this->output->writeln("<comment>✘</comment> <comment>There are no stable versions available</comment>");
        } else {
            $this->output->writeln("<comment>✘</comment> <comment>The latest version is already installed</comment>");
        }

        return Command::SUCCESS;
    }
}
