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

        foreach (['pengelola.dashboard', 'pengelola.piutang', 'pengelola.laporan-musdes', 'pengelola.verifikasi', 'pengelola.arus-kas', 'pengelola.modal-aset', 'pengelola.unit-usaha.index', 'pengelola.kewajiban.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_warga_cannot_access_operational_payables_page(): void
    {
        $warga = User::where('role', 'WARGA')->firstOrFail();
        $this->actingAs($warga)->get(route('pengelola.kewajiban.index'))->assertForbidden();
    }

    public function test_service_with_invoice_history_is_archived_and_remains_accessible(): void
    {
        $pengelola = User::where('role', 'PENGELOLA')->firstOrFail();
        $tagihan = Tagihan::firstOrFail();
        $layanan = $tagihan->layanan;

        $this->actingAs($pengelola)
            ->delete(route('pengelola.unit-usaha.layanan.destroy', [$layanan->unitUsaha, $layanan]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('layanan', ['id' => $layanan->id, 'status_aktif' => 0]);
        $this->assertDatabaseHas('tagihan', ['id' => $tagihan->id, 'layanan_id' => $layanan->id]);
        $this->assertSame('SELESAI', $tagihan->fresh()->status);

        $this->actingAs($pengelola)
            ->get(route('pengelola.unit-usaha.index'))
            ->assertOk()
            ->assertDontSee($layanan->nama_layanan);
    }

    public function test_unit_and_service_edit_routes_persist_changes(): void
    {
        $pengelola = User::where('role', 'PENGELOLA')->firstOrFail();
        $unit = \App\Models\UnitUsaha::firstOrFail();
        $layanan = $unit->layanan()->firstOrFail();

        $this->actingAs($pengelola)->put(route('pengelola.unit-usaha.update', $unit), [
            'nama_unit' => 'Unit Hasil Edit', 'penanggung_jawab' => 'PIC Baru', 'deskripsi' => 'Deskripsi baru', 'status_aktif' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('unit_usaha', ['id' => $unit->id, 'nama_unit' => 'Unit Hasil Edit', 'penanggung_jawab' => 'PIC Baru']);

        $this->put(route('pengelola.unit-usaha.layanan.update', [$unit, $layanan]), [
            'nama_layanan' => 'Layanan Hasil Edit', 'satuan' => 'Bulan', 'tarif_dasar' => '12345.00', 'deskripsi' => 'Tarif baru',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('layanan', ['id' => $layanan->id, 'nama_layanan' => 'Layanan Hasil Edit', 'tarif_dasar' => '12345']);
    }

    public function test_variable_and_one_off_services_store_without_a_billing_period(): void
    {
        $manager = User::where('role', 'PENGELOLA')->firstOrFail();
        $unit = \App\Models\UnitUsaha::firstOrFail();
        foreach ([['TAGIHAN_VARIABEL', null], ['SEKALI_BELI', '50000']] as [$kind, $price]) {
            $payload = ['nama_layanan' => 'Tes '.$kind, 'jenis_layanan' => $kind, 'tampil_di_katalog' => '1', 'deskripsi' => ''];
            if ($price !== null) $payload['tarif_dasar'] = $price;
            $this->actingAs($manager)->post(route('pengelola.unit-usaha.layanan.store', $unit), $payload)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('layanan', ['unit_usaha_id' => $unit->id, 'nama_layanan' => 'Tes '.$kind, 'jenis_layanan' => $kind]);
        }
    }

    public function test_edit_buttons_render_valid_double_quoted_alpine_attributes(): void
    {
        $pengelola = User::where('role', 'PENGELOLA')->firstOrFail();
        $response = $this->actingAs($pengelola)->get(route('pengelola.unit-usaha.index'))->assertOk();

        $response->assertSee('x-on:click="ubahUnit(JSON.parse(', false);
        $response->assertSee('x-on:click="ubahLayanan(', false);
    }

    public function test_pengelola_manual_payment_updates_invoice_audit_and_cash_once(): void
    {
        $pengelola = User::where('role', 'PENGELOLA')->firstOrFail();
        $tagihan = Tagihan::where('status', 'BELUM_DIBAYAR')->firstOrFail();
        $this->actingAs($pengelola)->post(route('pengelola.pembayaran-manual'), [
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran' => 'TUNAI_LOKET',
            'referensi_admin' => 'ADMIN-TEST-001',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tagihan', ['id' => $tagihan->id, 'status' => 'SELESAI']);
        $this->assertDatabaseHas('transaksi_pembayaran', ['tagihan_id' => $tagihan->id, 'dicatat_oleh' => $pengelola->id, 'status' => 'SELESAI']);
        $this->assertDatabaseHas('arus_kas', ['jenis' => 'PEMASUKAN', 'nominal' => $tagihan->nominal]);
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
