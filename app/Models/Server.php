<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property boolean $active
 * @property string $name
 * @property string $api
 * @property string $browse
 * @property string $processor
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read DeveloperServer $pivot
 */
class Server extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'api',
        'browse',
        'processor',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * The developer that belongs to the server.
     *
     * @return BelongsToMany
     */
    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(Developer::class);
    }
}
