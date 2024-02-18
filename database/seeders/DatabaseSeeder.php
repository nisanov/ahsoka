<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Developer;
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
            'active' => true,
            'name' => 'SPC Jira',
            'api' => 'http://jira.southernphone.net.au:8080/rest/api/2/search/',
            'processor' => SpcJira::class,
        ]);
        $servers->create([
            'active' => true,
            'name' => 'AGL Jira',
            'api' => 'https://aglenergy.atlassian.net/rest/api/3/search/',
            'processor' => AglJira::class,
        ]);
    }
}
