<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Models\Server\Type;
use App\Processors\Processor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property Type $type
 * @property string $name
 * @property string $api
 * @property string $token
 * @property boolean $active
 * @property Processor $processor
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
        'type',
        'name',
        'api',
        'token',
        'processor',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => Type::class,
        'token' => 'encrypted',
        'active' => 'boolean',
    ];

    /**
     * Get the server processor instance.
     *
     * @return Attribute
     */
    protected function processor(): Attribute
    {
        return Attribute::make(
            get: fn (string $processor) => app($processor),
        );
    }

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
