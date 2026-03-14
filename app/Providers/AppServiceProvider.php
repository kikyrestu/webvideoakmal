<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-create storage symlink for shared hosting (no SSH/exec needed)
        $link = public_path('storage');
        $target = storage_path('app/public');
        if (! is_link($link) && ! is_dir($link)) {
            @symlink($target, $link);
        }
    }
}
