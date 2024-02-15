<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;
use NunoMaduro\LaravelConsoleMenu\Menu;
use NunoMaduro\LaravelConsoleTask\LaravelConsoleTaskServiceProvider;

/**
 * @method bool task(string $title, $task = null, $loadingText = 'loading...')
 * @see LaravelConsoleTaskServiceProvider::boot()
 * @method Menu menu(string $title = '', array $options = [])
 * @see LaravelConsoleMenuServiceProvider::boot()
 */
abstract class Ahsoka extends Command
{
    /**
     * Get the configured application name.
     *
     * @return string
     */
    public function getApplicationName(): string
    {
        $app = Config::get('app.name');

        return "<fg=white;bg=blue> $app </>";
    }
}
