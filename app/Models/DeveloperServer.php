<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $developer_id
 * @property int $server_id
 * @property string $username
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DeveloperServer extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Define the models casting requirements.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'encrypted',
    ];
}
