<?php

declare(strict_types=1);

namespace App\Enums\Models\Holiday;

use App\Traits\ConvertsToArray;

enum Type: string
{
    use ConvertsToArray;

    case NATIONAL_HOLIDAY = 'National Holiday';
    case STATE_HOLIDAY = 'State Holiday';
    case STATE_BANK_HOLIDAY = 'State Bank Holiday';
}
