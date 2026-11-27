<?php

namespace App\Services;

use App\Models\ArusKas;
use App\Models\Tagihan;
use App\Models\TransaksiPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PembayaranManualService
{
    public function catat(int $tagihanId, int $pengelolaId, string $metode, ?string $referensi = null, ?string $catatan = null): TransaksiPembayaran
    {
        return DB::transaction(function () use ($tagihanId, $pengelolaId, $metode, $referensi, $catatan): TransaksiPembayaran {
            if ($referensi && TransaksiPembayaran::where('referensi_admin', $referensi)->exists()) {
                throw ValidationException::withMessages(['referensi_admin' => 'Referensi pencatatan sudah digunakan.']);
            }

            $tagihan = Tagihan::with('layanan')->lockForUpdate()->findOrFail($tagihanId);
            if ($tagihan->status !== 'BELUM_DIBAYAR') {
                throw ValidationException::withMessages(['tagihan_id' => 'Tagihan sudah memiliki pengajuan pembayaran atau telah lunas.']);
            }

            $now = now();
            $payment = TransaksiPembayaran::create([
                'kode_transaksi' => 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'referensi_admin' => $referensi, 'tagihan_id' => $tagihan->id, 'user_id' => $tagihan->user_id,
                'dicatat_oleh' => $pengelolaId, 'nominal_dibayar' => $tagihan->nominal,
                'metode_pembayaran' => $metode, 'status' => 'SELESAI', 'waktu_bayar' => $now,
                'waktu_konfirmasi' => $now, 'catatan_pengelola' => $catatan ?: 'Pembayaran diterima dan dicatat oleh pengelola.',
            ]);
            $tagihan->update(['status' => 'SELESAI']);
            ArusKas::create([
                'kode_kas' => 'KAS-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'unit_usaha_id' => $tagihan->layanan->unit_usaha_id, 'tanggal_transaksi' => $now->toDateString(),
                'jenis' => 'PEMASUKAN', 'sumber' => 'OTOMATIS_LAYANAN_WARGA', 'kategori_akun' => 'Pendapatan Jasa Layanan',
                'keterangan' => 'Pembayaran '.$tagihan->kode_tagihan.' dicatat oleh pengelola.',
                'nominal' => $tagihan->nominal, 'transaksi_pembayaran_id' => $payment->id, 'dicatat_oleh' => $pengelolaId,
            ]);
            return $payment->fresh(['tagihan']);
        });
    }
}
