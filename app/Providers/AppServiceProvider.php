<?php

namespace App\Providers;

use App\Models\Tagihan;
use App\Models\TransaksiPembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.app', function ($view) {
            $pengelolaAktif = Auth::check() && in_array(Auth::user()->role, ['PENGELOLA', 'ADMIN'], true);

            $view->with('sidebarPendingCount', $pengelolaAktif
                ? TransaksiPembayaran::where('status', 'MENUNGGU_KONFIRMASI')->count()
                : 0);
            $view->with('sidebarDebtorCount', $pengelolaAktif
                ? Tagihan::whereIn('status', ['BELUM_DIBAYAR', 'MENUNGGU_KONFIRMASI'])->distinct()->count('user_id')
                : 0);
        });
    }
}
