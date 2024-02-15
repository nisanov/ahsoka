<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $developer_id
 * @property int $server_id
 * @property string $username
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DeveloperServer extends Pivot
{
    //...
}
