<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
        // Definisikan hak akses berdasarkan role
        Gate::define('akses-admin', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('akses-kepsek', function (User $user) {
            return $user->hasRole('kepsek');
        });

        Gate::define('akses-guru', function (User $user) {
            return $user->hasRole('guru');
        });

        // Contoh kombinasi: Admin dan Kepsek bisa melihat laporan
        Gate::define('lihat-laporan', function (User $user) {
            return $user->hasAnyRole(['admin', 'kepsek']);
        });
    }
}
