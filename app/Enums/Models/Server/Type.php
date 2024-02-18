<?php

declare(strict_types=1);

namespace App\Enums\Models\Server;

use App\Traits\ConvertsToArray;

enum Type: string
{
    use ConvertsToArray;

    case JIRA = 'jira';
    case HOLIDAY = 'holiday';
}
