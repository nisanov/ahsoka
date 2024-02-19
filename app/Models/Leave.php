<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property int $developer_id
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Leave extends Model
{
    /**
     * Define the model's fillable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'developer_id',
        'starts_on',
        'ends_on',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];
}
