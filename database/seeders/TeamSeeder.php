<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Starter staff teams. firstOrCreate keeps any permission changes made in the admin.
 */
class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            'Management' => ['Full access to every business section.', array_keys(Team::PERMISSIONS)],
            'Sales' => ['Handles orders and prices quotation requests.', ['orders', 'messages', 'catalogue']],
            'Warehouse' => ['Keeps stock levels up to date.', ['inventory']],
            'Content & Marketing' => ['Edits pages, news and search settings.', ['content', 'seo', 'notifications', 'catalogue']],
        ];

        foreach ($teams as $name => [$description, $permissions]) {
            Team::firstOrCreate(['name' => $name], [
                'description' => $description,
                'permissions' => $permissions,
            ]);
        }

        // Management always has every section, including ones added later.
        Team::query()->where('name', 'Management')->update(['permissions' => array_keys(Team::PERMISSIONS)]);
    }
}
