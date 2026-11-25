<?php

namespace App\Services;

use App\Models\ArusKas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArusKasService
{
    public function catatManual(array $data, int $pengelolaId): ArusKas
    {
        return DB::transaction(function () use ($data, $pengelolaId) {
            do {
                $kode = 'KAS-M-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
            } while (ArusKas::where('kode_kas', $kode)->exists());

            return ArusKas::create([
                ...$data,
                'kode_kas' => $kode,
                'sumber' => 'INPUT_MANUAL_PENGELOLA',
                'transaksi_pembayaran_id' => null,
                'dicatat_oleh' => $pengelolaId,
            ]);
        });
    }
}
