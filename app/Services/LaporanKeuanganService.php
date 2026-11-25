<?php

namespace App\Services;

use App\Models\ArusKas;
use App\Models\AsetInventaris;
use App\Models\PenyertaanModal;
use App\Models\Tagihan;
use App\Models\TransaksiPembayaran;
use App\Models\UnitUsaha;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanKeuanganService
{
    public function ringkasanDashboard(Carbon $tanggalMulai, Carbon $tanggalAkhir, ?int $unitUsahaId = null): array
    {
        $arusPeriode = ArusKas::query()
            ->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]);
        if ($unitUsahaId) $arusPeriode->where('unit_usaha_id', $unitUsahaId);

        $pemasukan = (float) (clone $arusPeriode)->where('jenis', 'PEMASUKAN')->sum('nominal');
        $pengeluaran = (float) (clone $arusPeriode)->where('jenis', 'PENGELUARAN')->sum('nominal');

        $panjangPeriode = max(1, (int) $tanggalMulai->copy()->startOfDay()->diffInDays($tanggalAkhir->copy()->startOfDay()) + 1);
        $periodeSebelumnyaMulai = $tanggalMulai->copy()->startOfDay()->subDays($panjangPeriode);
        $periodeSebelumnyaAkhir = $tanggalMulai->copy()->startOfDay()->subDay();
        $arusSebelumnya = ArusKas::query()->whereBetween('tanggal_transaksi', [$periodeSebelumnyaMulai->toDateString(), $periodeSebelumnyaAkhir->toDateString()]);
        if ($unitUsahaId) $arusSebelumnya->where('unit_usaha_id', $unitUsahaId);
        $pemasukanSebelumnya = (float) (clone $arusSebelumnya)->where('jenis', 'PEMASUKAN')->sum('nominal');
        $pengeluaranSebelumnya = (float) (clone $arusSebelumnya)->where('jenis', 'PENGELUARAN')->sum('nominal');

        $arusSaldo = ArusKas::query()->whereDate('tanggal_transaksi', '<=', today()->toDateString());
        if ($unitUsahaId) $arusSaldo->where('unit_usaha_id', $unitUsahaId);
        $totalPemasukan = (float) (clone $arusSaldo)->where('jenis', 'PEMASUKAN')->sum('nominal');
        $totalPengeluaran = (float) (clone $arusSaldo)->where('jenis', 'PENGELUARAN')->sum('nominal');
        $saldoSebelumnya = (float) ArusKas::query()
            ->when($unitUsahaId, fn ($query) => $query->where('unit_usaha_id', $unitUsahaId))
            ->whereDate('tanggal_transaksi', '<=', $periodeSebelumnyaAkhir->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'PEMASUKAN' THEN nominal ELSE -nominal END), 0) as saldo")
            ->value('saldo');
        $persentasePerubahan = static fn (float $nilai, float $sebelumnya): float => $sebelumnya != 0.0
            ? round((($nilai - $sebelumnya) / abs($sebelumnya)) * 100, 1)
            : ($nilai > 0 ? 100.0 : 0.0);

        $tagihanTerbit = Tagihan::query()
            ->whereRaw('(periode_tahun * 100 + periode_bulan) between ? and ?', [(int) $tanggalMulai->format('Ym'), (int) $tanggalAkhir->format('Ym')]);
        if ($unitUsahaId) $tagihanTerbit->whereHas('layanan', fn ($query) => $query->where('unit_usaha_id', $unitUsahaId));
        $nominalTerbit = (float) (clone $tagihanTerbit)->sum('nominal');
        $nominalLunas = (float) (clone $tagihanTerbit)->where('status', 'SELESAI')->sum('nominal');

        $tagihanPiutang = Tagihan::query()->whereIn('status', ['BELUM_DIBAYAR', 'MENUNGGU_KONFIRMASI']);
        if ($unitUsahaId) $tagihanPiutang->whereHas('layanan', fn ($query) => $query->where('unit_usaha_id', $unitUsahaId));
        $totalPiutang = (float) $tagihanPiutang->sum('nominal');

        $bulanAkhir = $tanggalAkhir->copy()->endOfMonth();
        $bulanMulai = $bulanAkhir->copy()->startOfMonth()->subMonths(5);
        $barisTren = ArusKas::query()
            ->whereBetween('tanggal_transaksi', [$bulanMulai->toDateString(), $bulanAkhir->toDateString()])
            ->when($unitUsahaId, fn ($query) => $query->where('unit_usaha_id', $unitUsahaId))
            ->get(['tanggal_transaksi', 'jenis', 'nominal']);
        $trenPerBulan = [];
        $labelBulan = [];
        for ($i = 0; $i < 6; $i++) {
            $bulan = $bulanMulai->copy()->addMonths($i);
            $kunci = $bulan->format('Y-m');
            $trenPerBulan[$kunci] = ['PEMASUKAN' => 0.0, 'PENGELUARAN' => 0.0];
            $labelBulan[] = $bulan->locale('id')->translatedFormat('M y');
        }
        foreach ($barisTren as $baris) {
            $kunci = $baris->tanggal_transaksi->format('Y-m');
            if (isset($trenPerBulan[$kunci])) $trenPerBulan[$kunci][$baris->jenis] += (float) $baris->nominal;
        }

        $unitKinerja = UnitUsaha::query()->where('status_aktif', true)->orderBy('nama_unit')
            ->withSum(['arusKas as total_pemasukan' => fn ($query) => $query->where('jenis', 'PEMASUKAN')->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()])], 'nominal')
            ->withSum(['arusKas as total_pengeluaran' => fn ($query) => $query->where('jenis', 'PENGELUARAN')->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()])], 'nominal')
            ->get()
            ->map(fn (UnitUsaha $unit) => [
                'kode_unit' => $unit->kode_unit,
                'nama_unit' => $unit->nama_unit,
                'pemasukan' => (float) ($unit->total_pemasukan ?? 0),
                'pengeluaran' => (float) ($unit->total_pengeluaran ?? 0),
                'laba_bersih' => (float) ($unit->total_pemasukan ?? 0) - (float) ($unit->total_pengeluaran ?? 0),
            ]);

        return [
            'saldo_kas' => $totalPemasukan - $totalPengeluaran,
            'pemasukan_periode' => $pemasukan,
            'pengeluaran_periode' => $pengeluaran,
            'perubahan_pemasukan' => $persentasePerubahan($pemasukan, $pemasukanSebelumnya),
            'perubahan_pengeluaran' => $persentasePerubahan($pengeluaran, $pengeluaranSebelumnya),
            'perubahan_saldo' => $persentasePerubahan($totalPemasukan - $totalPengeluaran, $saldoSebelumnya),
            'laba_periode' => $pemasukan - $pengeluaran,
            'estimasi_pades' => max(0, round(($pemasukan - $pengeluaran) * 0.40, 2)),
            'total_piutang' => $totalPiutang,
            'nominal_tagihan_terbit' => $nominalTerbit,
            'nominal_tagihan_lunas' => $nominalLunas,
            'collection_rate' => $nominalTerbit > 0 ? ($nominalLunas / $nominalTerbit) * 100 : 0,
            'label_bulan' => $labelBulan,
            'tren_pemasukan' => array_column($trenPerBulan, 'PEMASUKAN'),
            'tren_pengeluaran' => array_column($trenPerBulan, 'PENGELUARAN'),
            'unit_kinerja' => $unitKinerja,
        ];
    }

    public function daftarPiutang(?int $unitUsahaId = null): Collection
    {
        $tagihan = Tagihan::with(['warga', 'layanan.unitUsaha'])
            ->whereIn('status', ['BELUM_DIBAYAR', 'MENUNGGU_KONFIRMASI'])
            ->when($unitUsahaId, fn ($query) => $query->whereHas('layanan', fn ($layanan) => $layanan->where('unit_usaha_id', $unitUsahaId)))
            ->orderBy('jatuh_tempo')
            ->get();

        return $tagihan->groupBy('user_id')->map(function (Collection $tagihanWarga) {
            $jatuhTempoTerlama = $tagihanWarga->first()->jatuh_tempo->copy()->startOfDay();
            $hariTerlambat = $jatuhTempoTerlama->isPast() ? (int) $jatuhTempoTerlama->diffInDays(today()) : 0;
            $bulanTertunggak = $hariTerlambat > 0 ? (int) floor($hariTerlambat / 30) : 0;

            return [
                'warga' => $tagihanWarga->first()->warga,
                'tagihan' => $tagihanWarga,
                'jumlah_tagihan' => $tagihanWarga->count(),
                'total_piutang' => (float) $tagihanWarga->sum(fn ($item) => (float) $item->nominal),
                'jatuh_tempo_terlama' => $jatuhTempoTerlama,
                'hari_terlambat' => $hariTerlambat,
                'bulan_tertunggak' => $bulanTertunggak,
            ];
        })->sortByDesc('total_piutang')->values();
    }

    public function aktivitasTerbaru(Carbon $tanggalMulai, Carbon $tanggalAkhir, ?int $unitUsahaId = null, int $jumlah = 8): Collection
    {
        $arusKas = ArusKas::with('unitUsaha')
            ->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()])
            ->when($unitUsahaId, fn ($query) => $query->where('unit_usaha_id', $unitUsahaId))
            ->latest('tanggal_transaksi')->latest('id')->take($jumlah)->get()
            ->map(fn (ArusKas $item) => [
                'tanggal' => $item->tanggal_transaksi,
                'waktu' => $item->created_at,
                'aktivitas' => $item->kategori_akun,
                'unit' => $item->unitUsaha->nama_unit,
                'nominal' => (float) $item->nominal,
                'jenis' => $item->jenis,
                'status' => $item->sumber === 'OTOMATIS_LAYANAN_WARGA' ? 'LUNAS' : ($item->jenis === 'PENGELUARAN' ? 'KELUAR' : 'MASUK'),
                'keterangan' => $item->sumber === 'OTOMATIS_LAYANAN_WARGA' ? 'Pembayaran tagihan terkonfirmasi' : 'Pencatatan kas manual',
                'kode' => $item->kode_kas,
            ]);

        $pengajuan = TransaksiPembayaran::with(['tagihan.layanan.unitUsaha'])
            ->where('status', 'MENUNGGU_KONFIRMASI')
            ->whereBetween('waktu_bayar', [$tanggalMulai->copy()->startOfDay(), $tanggalAkhir->copy()->endOfDay()])
            ->when($unitUsahaId, fn ($query) => $query->whereHas('tagihan.layanan', fn ($layanan) => $layanan->where('unit_usaha_id', $unitUsahaId)))
            ->latest('waktu_bayar')->take($jumlah)->get()
            ->map(fn (TransaksiPembayaran $item) => [
                'tanggal' => $item->waktu_bayar,
                'waktu' => $item->waktu_bayar,
                'aktivitas' => 'Pengajuan · '.$item->tagihan->layanan->nama_layanan,
                'unit' => $item->tagihan->layanan->unitUsaha->nama_unit,
                'nominal' => (float) $item->nominal_dibayar,
                'jenis' => 'PENGAJUAN',
                'status' => 'MENUNGGU',
                'keterangan' => 'Belum tercatat sebagai kas',
                'kode' => $item->kode_transaksi,
            ]);

        return $arusKas->concat($pengajuan)->sortByDesc('tanggal')->take($jumlah)->values();
    }

    public function laporanMusdes(Carbon $tanggalMulai, Carbon $tanggalAkhir): array
    {
        $arus = ArusKas::query()->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]);
        $pemasukan = (float) (clone $arus)->where('jenis', 'PEMASUKAN')->sum('nominal');
        $pengeluaran = (float) (clone $arus)->where('jenis', 'PENGELUARAN')->sum('nominal');
        $labaBersih = $pemasukan - $pengeluaran;

        $unit = UnitUsaha::query()->where('status_aktif', true)->orderBy('nama_unit')
            ->withSum(['arusKas as total_pemasukan' => fn ($query) => $query->where('jenis', 'PEMASUKAN')->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()])], 'nominal')
            ->withSum(['arusKas as total_pengeluaran' => fn ($query) => $query->where('jenis', 'PENGELUARAN')->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()])], 'nominal')
            ->get()
            ->map(fn (UnitUsaha $item) => [
                'nama_unit' => $item->nama_unit,
                'pemasukan' => (float) ($item->total_pemasukan ?? 0),
                'pengeluaran' => (float) ($item->total_pengeluaran ?? 0),
                'laba_bersih' => (float) ($item->total_pemasukan ?? 0) - (float) ($item->total_pengeluaran ?? 0),
            ]);

        $alokasi = ['pades' => 0.0, 'penambahan_modal' => 0.0, 'insentif_pengurus' => 0.0, 'dana_sosial' => 0.0];
        if ($labaBersih > 0) {
            $alokasi['pades'] = round($labaBersih * 0.40, 2);
            $alokasi['penambahan_modal'] = round($labaBersih * 0.30, 2);
            $alokasi['insentif_pengurus'] = round($labaBersih * 0.20, 2);
            $alokasi['dana_sosial'] = round($labaBersih - array_sum($alokasi), 2);
        }

        return [
            'total_modal' => (float) PenyertaanModal::sum('nominal'),
            'total_aset' => (float) AsetInventaris::whereIn('kondisi', ['BAIK', 'PERLU_PERBAIKAN'])->sum('nilai_perolehan'),
            'daftar_modal' => PenyertaanModal::with('unitUsaha')->orderBy('tanggal_terima')->get(),
            'daftar_aset' => AsetInventaris::with('unitUsaha')
                ->whereIn('kondisi', ['BAIK', 'PERLU_PERBAIKAN'])
                ->orderBy('tanggal_perolehan')->get(),
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'laba_bersih' => $labaBersih,
            'unit_kinerja' => $unit,
            'alokasi' => $alokasi,
            'alokasi_tersedia' => $labaBersih > 0,
        ];
    }
}
