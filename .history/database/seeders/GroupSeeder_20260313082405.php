<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'Mabes',      'type' => 'mabes',  'sort_order' => 1],
            ['name' => 'Polda',      'type' => 'polda',  'sort_order' => 2],
            ['name' => 'Polres',     'type' => 'polres', 'sort_order' => 3],
            ['name' => 'Lainnya',    'type' => 'other',  'sort_order' => 4],
        ];

        foreach ($groups as $group) {
            Group::updateOrCreate(
                ['slug' => Str::slug($group['name'])],
                [
                    'name'       => $group['name'],
                    'type'       => $group['type'],
                    'sort_order' => $group['sort_order'],
                ]
            );
        }
    }
}
