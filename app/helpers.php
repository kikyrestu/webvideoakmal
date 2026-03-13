<?php

if (! function_exists('setting')) {
    /**
     * Get a site setting value from cache or DB.
     * Cache key: 'site_settings' — busted when admin saves.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $settings = cache()->rememberForever('site_settings', function () {
            return \App\Models\SiteSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}
