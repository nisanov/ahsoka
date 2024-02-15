<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property Carbon $month
 * @property string $ticket
 * @property string $summary
 * @property int $points
 * @property int $coverage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Issue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_id',
        'developer_id',
        'month',
        'ticket',
        'summary',
        'points',
        'coverage',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'month' => 'date',
        'points' => 'integer',
        'coverage' => 'integer',
    ];

    /**
     * Get the server this issue belongs to.
     *
     * @return BelongsTo
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the developer this issue belongs to.
     *
     * @return BelongsTo
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }
}
