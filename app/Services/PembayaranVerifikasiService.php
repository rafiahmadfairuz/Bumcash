<?php

namespace App\Services;

use App\Models\ArusKas;
use App\Models\Tagihan;
use App\Models\TransaksiPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PembayaranVerifikasiService
{
    public function konfirmasiLunas(int $transaksiId, int $pengelolaId): TransaksiPembayaran
    {
        return DB::transaction(function () use ($transaksiId, $pengelolaId) {
            $transaksi = TransaksiPembayaran::lockForUpdate()->findOrFail($transaksiId);
            $tagihan = Tagihan::lockForUpdate()->findOrFail($transaksi->tagihan_id);

            if ($transaksi->status !== 'MENUNGGU_KONFIRMASI' || $tagihan->status !== 'MENUNGGU_KONFIRMASI') {
                throw ValidationException::withMessages(['transaksi' => 'Transaksi ini sudah diproses atau status tagihannya berubah.']);
            }

            $tagihan->load('layanan');

            $waktuKonfirmasi = now();
            $transaksi->update(['status' => 'SELESAI', 'waktu_konfirmasi' => $waktuKonfirmasi, 'catatan_pengelola' => 'Dikonfirmasi lunas oleh Pengelola BUMDes.']);
            $tagihan->update(['status' => 'SELESAI']);

            ArusKas::create([
                'kode_kas' => $this->kodeKasOtomatis(),
                'unit_usaha_id' => $tagihan->layanan->unit_usaha_id,
                'tanggal_transaksi' => $waktuKonfirmasi->toDateString(),
                'jenis' => 'PEMASUKAN',
                'sumber' => 'OTOMATIS_LAYANAN_WARGA',
                'kategori_akun' => 'Pendapatan Jasa Layanan',
                'keterangan' => 'Pembayaran tagihan '.$tagihan->kode_tagihan.' telah dikonfirmasi.',
                'nominal' => $transaksi->nominal_dibayar,
                'transaksi_pembayaran_id' => $transaksi->id,
                'dicatat_oleh' => $pengelolaId,
            ]);

            return $transaksi->fresh(['tagihan']);
        });
    }

    public function tolak(int $transaksiId, string $catatan): TransaksiPembayaran
    {
        return DB::transaction(function () use ($transaksiId, $catatan) {
            $transaksi = TransaksiPembayaran::lockForUpdate()->findOrFail($transaksiId);
            $tagihan = Tagihan::lockForUpdate()->findOrFail($transaksi->tagihan_id);

            if ($transaksi->status !== 'MENUNGGU_KONFIRMASI' || $tagihan->status !== 'MENUNGGU_KONFIRMASI') {
                throw ValidationException::withMessages(['transaksi' => 'Transaksi ini sudah diproses atau status tagihannya berubah.']);
            }

            $transaksi->update([
                'status' => 'DITOLAK',
                'waktu_konfirmasi' => now(),
                'catatan_pengelola' => $catatan,
            ]);
            $tagihan->update(['status' => 'BELUM_DIBAYAR']);

            return $transaksi->fresh(['tagihan']);
        });
    }

    private function kodeKasOtomatis(): string
    {
        do {
            $kode = 'KAS-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        } while (ArusKas::where('kode_kas', $kode)->exists());

        return $kode;
    }
}
