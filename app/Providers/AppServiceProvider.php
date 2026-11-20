<?php

namespace App\Providers;

use App\Models\DigitalServiceDetail;
use App\Models\Sale;
use App\Models\User;
use App\Policies\DigitalServiceDetailPolicy;
use App\Policies\SalePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('id');

        Paginator::useBootstrapFive();

        Model::preventLazyLoading(app()->isLocal());

        config([
            'adminlte.title' => config('store.name'),
            'adminlte.title_postfix' => '',
            'adminlte.logo' => '<div class="brand-wrapper d-inline-flex align-items-center gap-2 text-start"><i class="bi bi-shop brand-icon text-success fs-4"></i><div><span class="brand-title fw-bold tracking-tight">PUTRA MANDIRI</span><span class="brand-subtitle d-block text-success-emphasis" style="font-size: 0.68rem; line-height: 1; letter-spacing: 0.5px;">MINIMARKET 24 JAM</span></div></div>',
            'adminlte.logo_img' => null,
            'adminlte.primary_color' => '#15803d',
            'adminlte.usermenu_profile_url' => false,
            'adminlte.sidebar_docs_url' => null,
            'adminlte.sidebar_mini' => false,
            'adminlte.sidebar_collapse' => false,
            'adminlte.sidebar_collapse_auto_size' => false,
            'adminlte.color_mode_toggle' => false,
            'adminlte.menu' => config('navigation.menu'),
        ]);

        Gate::define('use-pos', fn (User $user): bool => $user->isOwner() || $user->isKasir());
        Gate::define('view-sales', fn (User $user): bool => $user->isOwner() || $user->isKasir());
        Gate::define('manage-catalog', fn (User $user): bool => $user->isOwner());
        Gate::define('view-reports', fn (User $user): bool => $user->isOwner());
        Gate::define('manage-expenses', fn (User $user): bool => $user->isOwner());
        Gate::define('manage-users', fn (User $user): bool => $user->isOwner());

        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(DigitalServiceDetail::class, DigitalServiceDetailPolicy::class);
    }
}
