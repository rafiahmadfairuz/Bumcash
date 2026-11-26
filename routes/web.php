<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\DashboardKeuanganController;
use App\Http\Controllers\PembayaranWargaController;
use App\Http\Controllers\PengelolaKeuanganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware(['auth', 'throttle:6,1'])->group(function (): void {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');
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

Route::prefix('warga')->name('warga.')->middleware(['auth', 'verified', 'role:WARGA'])->group(function (): void {
    Route::get('/', [PembayaranWargaController::class, 'index'])->name('dashboard');
    Route::get('/tagihan/{tagihan}', [PembayaranWargaController::class, 'detail'])->whereNumber('tagihan')->name('tagihan.detail');
    Route::post('/tagihan/{tagihan}/pembayaran', [PembayaranWargaController::class, 'ajukanPembayaran'])->whereNumber('tagihan')->name('tagihan.pembayaran');
    Route::get('/riwayat', [PembayaranWargaController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat/{transaksi}/bukti', [PembayaranWargaController::class, 'struk'])->whereNumber('transaksi')->name('bukti-pembayaran');
});
