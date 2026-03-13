<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Short',     'color' => '#8B5CF6'],
            ['name' => 'Live',      'color' => '#EF4444'],
            ['name' => 'Peristiwa', 'color' => '#F59E0B'],
            ['name' => 'Event',     'color' => '#10B981'],
            ['name' => 'Umum',      'color' => '#6B7280'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'color' => $cat['color']]
            );
        }
    }
}
