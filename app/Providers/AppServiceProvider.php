<?php

declare(strict_types=1);

namespace App\Providers;

use App\Commands\SystemUpdateCommand;
use Humbug\SelfUpdate\Updater;
use Illuminate\Support\ServiceProvider;
use LaravelZero\Framework\Components\Updater\Strategy\GithubStrategy;
use Phar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->isProductionPhar()) {
            $this->commands([SystemUpdateCommand::class]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->isProductionPhar()) {
            $this->app->singleton(Updater::class, function (): Updater {

                $strategy = new GithubStrategy();
                $strategy->setPackageName('nisanov/ahsoka');
                $strategy->setCurrentLocalVersion(config('app.version'));

                $updater = new Updater($this->getPharBuildPath(), false, Updater::STRATEGY_GITHUB);
                $updater->setStrategyObject($strategy);

                return $updater;
            });
        }
    }

    /**
     * Checks if the application is running as a production Phar archive.
     *
     * @return bool Returns true if the application is running as a production Phar archive, false otherwise.
     */
    private function isProductionPhar(): bool
    {
        return Phar::running() !== '' && $this->app->environment('production');
    }

    /**
     * Retrieves the path to the build of the current Phar archive.
     *
     * @return string The path to the build of the current Phar archive.
     */
    private function getPharBuildPath(): string
    {
        return Phar::running(false);
    }
}
