<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\TransaksiPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanKeuanganPengelolaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_seluruh_halaman_pengelola_berhasil_dirender_dengan_data_demo(): void
    {
        $pengelola = User::where('role', 'PENGELOLA')->firstOrFail();
        $this->actingAs($pengelola);

        foreach (['pengelola.dashboard', 'pengelola.piutang', 'pengelola.laporan-musdes', 'pengelola.verifikasi', 'pengelola.arus-kas', 'pengelola.modal-aset'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_seluruh_halaman_warga_tetap_berhasil_dirender(): void
    {
        $warga = User::where('role', 'WARGA')->firstOrFail();
        $this->actingAs($warga);

        $this->get(route('warga.dashboard'))->assertOk();
        $this->get(route('warga.riwayat'))->assertOk();

        $tagihan = Tagihan::where('user_id', $warga->id)->firstOrFail();
        $this->get(route('warga.tagihan.detail', $tagihan))->assertOk();

        $transaksiLunas = TransaksiPembayaran::where('user_id', $warga->id)->where('status', 'SELESAI')->first();
        if ($transaksiLunas) {
            $this->get(route('warga.bukti-pembayaran', $transaksiLunas))->assertOk();
        }
    }
}
