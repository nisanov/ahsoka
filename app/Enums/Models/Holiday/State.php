<?php

declare(strict_types=1);

namespace App\Enums\Models\Holiday;

use App\Traits\ConvertsToArray;

enum State: string
{
    use ConvertsToArray;

    case AUSTRALIAN_CAPITAL_TERRITORY = 'Australian Capital Territory';
    case NEW_SOUTH_WALES = 'New South Wales';
    case NORTHERN_TERRITORY = 'Northern Territory';
    case QUEENSLAND = 'Queensland';
    case SOUTH_AUSTRALIA = 'South Australia';
    case TASMANIA = 'Tasmania';
    case VICTORIA = 'Victoria';
    case WESTERN_AUSTRALIA = 'Western Australia';
}
