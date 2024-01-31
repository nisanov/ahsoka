<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

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
        if (empty($app)) {
            throw new RuntimeException("The application name must be configured.");
        }

        return $app;
    }
}
