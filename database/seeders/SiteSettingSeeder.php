<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name'        => 'Video Portal',
            'site_logo'        => '',
            'site_favicon'     => '',
            'site_description' => 'Portal video resmi',
            'sidebar_menu'     => json_encode([
                ['label' => 'Beranda',   'icon' => 'heroicon-o-home',        'url' => '/'],
                ['label' => 'Live',      'icon' => 'heroicon-o-signal',      'url' => '/category/live'],
                ['label' => 'Peristiwa','icon' => 'heroicon-o-newspaper',    'url' => '/category/peristiwa'],
                ['label' => 'Event',    'icon' => 'heroicon-o-calendar',     'url' => '/category/event'],
            ]),
            'nav_filter_labels' => json_encode(['Info', 'Umum']),
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Bust setting cache agar fresh dari DB
        cache()->forget('site_settings');
    }
}
