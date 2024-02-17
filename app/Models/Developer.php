<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Models\Holiday\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property string $name
 * @property State $state
 * @property int $points_per_week
 * @property int $coverage_per_week
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection<Server> $servers
 */
class Developer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'state',
        'points_per_week',
        'coverage_per_week',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'state' => State::class,
        'points_per_week' => 'integer',
        'coverage_per_week' => 'integer',
    ];

    /**
     * Get the servers related to this developer instance.
     *
     * @return BelongsToMany
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class)->using(DeveloperServer::class)->withPivot('username')->withTimestamps();
    }

    /**
     * Get the issues related to this developer instance.
     *
     * @return HasMany
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }
}
