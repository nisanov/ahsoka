<?php

declare(strict_types=1);

namespace App\Enums\Menu;

use App\Traits\ConvertsToArray;

enum Prompt: string
{
    use ConvertsToArray;

    case INFO = 'info';
    case ALERT = 'alert';
    case SUCCESS = 'success';

    /**
     * Get the background color for the prompt.
     *
     * @return string
     */
    public function background(): string
    {
        return match ($this) {
            Prompt::INFO => 'black',
            Prompt::ALERT => 'red',
            Prompt::SUCCESS => 'green',
        };
    }

    /**
     * Get the foreground color for the prompt.
     *
     * @return string
     */
    public function foreground(): string
    {
        return match ($this) {
            Prompt::INFO,
            Prompt::ALERT,
            Prompt::SUCCESS => 'white',
        };
    }
}
