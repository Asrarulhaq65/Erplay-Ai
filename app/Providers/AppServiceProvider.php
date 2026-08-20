<?php

namespace App\Providers;

use App\Models\AkunAkuntansi;
use App\Models\Produk;
use App\Policies\AkunAkuntansiPolicy;
use App\Policies\ProdukPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Produk::class, ProdukPolicy::class);
        Gate::policy(AkunAkuntansi::class, AkunAkuntansiPolicy::class);
    }
}
