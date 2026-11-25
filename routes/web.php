<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardKeuanganController;
use App\Http\Controllers\PembayaranWargaController;
use App\Http\Controllers\PengelolaKeuanganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::prefix('pengelola')->name('pengelola.')->middleware(['auth', 'role:PENGELOLA,ADMIN'])->group(function (): void {
    Route::get('/', [DashboardKeuanganController::class, 'index'])->name('dashboard');
    Route::get('/piutang', [DashboardKeuanganController::class, 'piutang'])->name('piutang');
    Route::get('/laporan-musdes', [DashboardKeuanganController::class, 'laporanMusdes'])->name('laporan-musdes');
    Route::get('/verifikasi', [PengelolaKeuanganController::class, 'verifikasi'])->name('verifikasi');
    Route::post('/verifikasi/{transaksi}/konfirmasi', [PengelolaKeuanganController::class, 'konfirmasi'])->whereNumber('transaksi')->name('verifikasi.konfirmasi');
    Route::post('/verifikasi/{transaksi}/tolak', [PengelolaKeuanganController::class, 'tolak'])->whereNumber('transaksi')->name('verifikasi.tolak');
    Route::get('/arus-kas', [PengelolaKeuanganController::class, 'arusKas'])->name('arus-kas');
    Route::post('/arus-kas', [PengelolaKeuanganController::class, 'simpanArusKas'])->name('arus-kas.simpan');
    Route::get('/modal-aset', [PengelolaKeuanganController::class, 'modalAset'])->name('modal-aset');
    Route::post('/modal-aset/modal', [PengelolaKeuanganController::class, 'simpanModal'])->name('modal-aset.modal');
    Route::patch('/modal-aset/modal/{modal}/alokasi', [PengelolaKeuanganController::class, 'alokasikanModal'])->whereNumber('modal')->name('modal-aset.alokasi');
    Route::post('/modal-aset/aset', [PengelolaKeuanganController::class, 'simpanAset'])->name('modal-aset.aset');
});

Route::prefix('warga')->name('warga.')->middleware(['auth', 'role:WARGA'])->group(function (): void {
    Route::get('/', [PembayaranWargaController::class, 'index'])->name('dashboard');
    Route::get('/tagihan/{tagihan}', [PembayaranWargaController::class, 'detail'])->whereNumber('tagihan')->name('tagihan.detail');
    Route::post('/tagihan/{tagihan}/pembayaran', [PembayaranWargaController::class, 'ajukanPembayaran'])->whereNumber('tagihan')->name('tagihan.pembayaran');
    Route::get('/riwayat', [PembayaranWargaController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat/{transaksi}/bukti', [PembayaranWargaController::class, 'struk'])->whereNumber('transaksi')->name('bukti-pembayaran');
});
