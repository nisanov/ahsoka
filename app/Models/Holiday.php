<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Models\Holiday\State;
use App\Enums\Models\Holiday\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $token
 * @property Type $type
 * @property State $state
 * @property Carbon $date
 *
 * @method Builder ofState(State $state)
 * @see Holiday::scopeOfState()
 */
class Holiday extends Model
{
    /**
     * Define the model's fillable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'token',
        'type',
        'state',
        'date',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => Type::class,
        'state' => State::class,
        'date' => 'date',
    ];

    /**
     * Define the state scope.
     *
     * @noinspection PhpUnused
     */
    public function scopeOfState(Builder $query, State $state): void
    {
        $query
            ->whereRaw('date BETWEEN CURRENT_DATE - INTERVAL 3 MONTH AND CURRENT_DATE + INTERVAL 6 MONTH')
            ->where(function (Builder $query) use ($state) {
                $query->where(['state' => $state])->orWhereNull('state');
            });
    }
}
