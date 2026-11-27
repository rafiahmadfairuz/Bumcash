<?php

namespace App\Services;

use App\Models\ArusKas;
use App\Models\PiutangUsaha;
use App\Models\SettlementUsaha;
use App\Models\TagihanUtilitas;
use App\Models\UnitUsaha;
use App\Models\UtangUsaha;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PembukuanUsahaService
{
    public function catatUtilitas(array $data, int $pengelolaId): TagihanUtilitas
    {
        return DB::transaction(function () use ($data, $pengelolaId): TagihanUtilitas {
            $total = $this->cents($data['nominal']) + $this->cents($data['biaya_tambahan'] ?? '0');
            $bill = TagihanUtilitas::create([...$data, 'dicatat_oleh' => $pengelolaId, 'status' => 'TERCATAT']);
            UtangUsaha::create([
                'unit_usaha_id' => $bill->unit_usaha_id, 'tagihan_utilitas_id' => $bill->id,
                'dicatat_oleh' => $pengelolaId, 'pihak' => $bill->nama_penyedia,
                'referensi_dokumen' => $bill->referensi_tagihan, 'deskripsi' => 'Tagihan '.$bill->kategori.' periode '.($bill->periode ?? '-'),
                'tanggal_transaksi' => $bill->tanggal_tagihan, 'jatuh_tempo' => $bill->jatuh_tempo,
            'nominal' => $this->decimal($total), 'saldo' => $this->decimal($total),
            ]);
            return $bill;
        });
    }

    public function catatPiutang(array $data, int $pengelolaId): PiutangUsaha
    {
        return DB::transaction(fn () => PiutangUsaha::create([...$data, 'dicatat_oleh' => $pengelolaId, 'nominal' => $data['nominal'], 'saldo' => $data['nominal'], 'status' => 'TERCATAT']));
    }

    public function catatUtang(array $data, int $pengelolaId): UtangUsaha
    {
        return DB::transaction(fn () => UtangUsaha::create([...$data, 'dicatat_oleh' => $pengelolaId, 'nominal' => $data['nominal'], 'saldo' => $data['nominal'], 'status' => 'TERCATAT']));
    }

    public function settlement(string $jenis, int $id, array $data, int $pengelolaId): SettlementUsaha
    {
        if (! in_array($jenis, ['piutang', 'utang'], true)) throw new \InvalidArgumentException('Jenis settlement tidak dikenal.');

        return DB::transaction(function () use ($jenis, $id, $data, $pengelolaId): SettlementUsaha {
            $model = $jenis === 'piutang' ? PiutangUsaha::class : UtangUsaha::class;
            $record = $model::query()->lockForUpdate()->findOrFail($id);
            $amount = $this->cents($data['nominal']);
            $balance = $this->cents($record->saldo);
            if ($amount < 1 || $amount > $balance) throw ValidationException::withMessages(['nominal' => 'Nominal settlement harus lebih dari nol dan tidak melebihi saldo.']);

            $remaining = $balance - $amount;
            $record->saldo = $this->decimal($remaining);
            $record->status = $remaining === 0 ? 'LUNAS' : 'SEBAGIAN';
            $record->save();

            $unit = UnitUsaha::findOrFail($record->unit_usaha_id);
            $kas = ArusKas::create([
                'kode_kas' => 'KAS-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'unit_usaha_id' => $unit->id, 'tanggal_transaksi' => $data['tanggal_settlement'],
                'jenis' => $jenis === 'piutang' ? 'PEMASUKAN' : 'PENGELUARAN',
                'sumber' => 'INPUT_MANUAL_PENGELOLA',
                'kategori_akun' => $jenis === 'piutang' ? 'Pelunasan Piutang Usaha' : 'Pembayaran Utang Usaha',
                'keterangan' => 'Settlement '.($record->referensi_dokumen ?: '#'.$record->id).' · '.$record->pihak,
                'nominal' => $this->decimal($amount), 'dicatat_oleh' => $pengelolaId,
            ]);

            $settlement = SettlementUsaha::create([
                'jenis_entitas' => $jenis, 'entitas_id' => $record->id, 'dicatat_oleh' => $pengelolaId,
                'tanggal_settlement' => $data['tanggal_settlement'], 'nominal' => $this->decimal($amount),
                'metode' => $data['metode'], 'referensi' => $data['referensi'] ?? null,
                'arus_kas_id' => $kas->id, 'catatan' => $data['catatan'] ?? null,
            ]);

            if ($jenis === 'utang' && $record->tagihan_utilitas_id && $remaining === 0) {
                TagihanUtilitas::whereKey($record->tagihan_utilitas_id)->update([
                    'status' => 'DIBAYAR', 'referensi_pembayaran' => $data['referensi'] ?? null,
                    'waktu_pembayaran' => $data['tanggal_settlement'],
                ]);
            } elseif ($jenis === 'utang' && $record->tagihan_utilitas_id) {
                TagihanUtilitas::whereKey($record->tagihan_utilitas_id)->update(['status' => 'DIBAYAR_SEBAGIAN']);
            }

            return $settlement;
        });
    }

    private function cents(string|int|float $value): int
    {
        $value = (string) $value;
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $value, $parts)) {
            throw ValidationException::withMessages(['nominal' => 'Format nominal tidak valid.']);
        }
        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '0', 2, '0');
    }

    private function decimal(int $cents): string { return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT); }
}
