<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Models\Server\Type;
use App\Models\Server;
use App\Processors\AglJira;
use App\Processors\SpcJira;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $servers = new Server();
        $servers->create([
            'type' => Type::JIRA,
            'name' => 'SPC Jira',
            'api' => 'http://jira.southernphone.net.au:8080/rest/api/2/search/',
            'token' => null,
            'processor' => SpcJira::class,
            'active' => true,
        ]);
        $servers->create([
            'type' => Type::JIRA,
            'name' => 'AGL Jira',
            'api' => 'https://aglenergy.atlassian.net/rest/api/3/search/',
            'token' => null,
            'processor' => AglJira::class,
            'active' => true,
        ]);
        $servers->create([
            'type' => Type::HOLIDAY,
            'name' => 'Calendarific',
            'api' => 'https://calendarific.com/api/v2/holidays/',
            'token' => null,
            'processor' => null,
            'active' => true,
        ]);
    }
}
