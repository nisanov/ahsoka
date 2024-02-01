<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;

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
