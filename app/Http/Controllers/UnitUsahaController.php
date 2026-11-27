<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\UnitUsaha;
use App\Models\PermintaanTagihanVariabel;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UnitUsahaController extends Controller
{
    public function index(): View
    {
        $units = UnitUsaha::query()
            ->with([
                'layanan' => fn ($query) => $query->where('status_aktif', true)->orderBy('nama_layanan'),
            ])
            ->withCount(['layanan' => fn ($query) => $query->where('status_aktif', true)])
            ->withSum(['arusKas as total_pemasukan' => fn ($query) => $query->where('jenis', 'PEMASUKAN')], 'nominal')
            ->withSum(['arusKas as total_pengeluaran' => fn ($query) => $query->where('jenis', 'PENGELUARAN')], 'nominal')
            ->orderByDesc('status_aktif')
            ->orderBy('nama_unit')
            ->get();

        $units->each(function (UnitUsaha $unit): void {
            $unit->laba_bersih = (float) ($unit->total_pemasukan ?? 0) - (float) ($unit->total_pengeluaran ?? 0);
        });

        $tertinggi = $units->where('status_aktif', true)->where('laba_bersih', '>', 0)->sortByDesc('laba_bersih')->first();

        return view('pengelola.unit-usaha.index', [
            'unitUsaha' => $units,
            'totalUnit' => $units->count(),
            'totalLayananAktif' => $units->where('status_aktif', true)->sum('layanan_count'),
            'unitSurplusTertinggi' => $tertinggi,
            'permintaanVariabel' => PermintaanTagihanVariabel::with(['warga', 'layanan.unitUsaha'])->where('status', 'MENUNGGU_PENGECEKAN')->latest()->take(100)->get(),
        ]);
    }

    public function terbitkanTagihanVariabel(Request $request, int $permintaan): RedirectResponse
    {
        $data = $request->validate(['nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'], 'catatan' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($permintaan, $data): void {
            $item = PermintaanTagihanVariabel::query()->lockForUpdate()->findOrFail($permintaan);
            abort_unless($item->status === 'MENUNGGU_PENGECEKAN', 422);
            $tagihan = Tagihan::create(['kode_tagihan' => 'VAR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)), 'user_id' => $item->user_id, 'layanan_id' => $item->layanan_id, 'periode_bulan' => now()->month, 'periode_tahun' => now()->year, 'rincian_pemakaian' => 'Tagihan terverifikasi · '.$item->nomor_objek, 'nominal' => $data['nominal'], 'jatuh_tempo' => now()->addDays(7)->toDateString(), 'status' => 'BELUM_DIBAYAR']);
            $item->update(['status' => 'TAGIHAN_DITERBITKAN', 'nominal' => $data['nominal'], 'tagihan_id' => $tagihan->id, 'diproses_oleh' => auth()->id(), 'catatan' => $data['catatan'] ?? null]);
        });
        return back()->with('success', 'Nominal aktual sudah diterbitkan sebagai tagihan warga.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiUnit($request);
        $data['kode_unit'] = $this->kodeUnitTersedia($data['nama_unit']);

        UnitUsaha::create($data);

        return back()->with('success', 'Unit Usaha berhasil ditambahkan.');
    }

    public function update(Request $request, UnitUsaha $unitUsaha): RedirectResponse
    {
        $unitUsaha->update($this->validasiUnit($request));

        return back()->with('success', 'Informasi Unit Usaha berhasil diperbarui.');
    }

    public function destroy(UnitUsaha $unitUsaha): RedirectResponse
    {
        if ($unitUsaha->layanan()->exists()
            || $unitUsaha->arusKas()->exists()
            || $unitUsaha->asetInventaris()->exists()
            || $unitUsaha->penyertaanModal()->exists()) {
            return back()->withErrors([
                'unitUsaha' => 'Unit ini sudah memiliki layanan atau catatan keuangan/aset. Nonaktifkan unit agar riwayat tetap utuh.',
            ]);
        }

        $unitUsaha->delete();

        return back()->with('success', 'Unit Usaha berhasil dihapus.');
    }

    public function storeLayanan(Request $request, UnitUsaha $unitUsaha): RedirectResponse
    {
        abort_unless($unitUsaha->status_aktif, 422, 'Unit usaha nonaktif tidak dapat menerima layanan baru.');
        $unitUsaha->layanan()->create($this->validasiLayanan($request));

        return back()->with('success', 'Layanan dan tarif berhasil ditambahkan pada Unit Usaha.');
    }

    public function updateLayanan(Request $request, UnitUsaha $unitUsaha, int $layanan): RedirectResponse
    {
        $item = $unitUsaha->layanan()->findOrFail($layanan);
        $request->merge([
            'jenis_layanan' => $request->input('jenis_layanan', $item->jenis_layanan ?: 'BULANAN'),
            'tampil_di_katalog' => $request->boolean('tampil_di_katalog', (bool) $item->tampil_di_katalog),
        ]);
        $data = $this->validasiLayanan($request);
        $item->update($data);

        return back()->with('success', 'Layanan dan tarif berhasil diperbarui.');
    }

    public function destroyLayanan(UnitUsaha $unitUsaha, int $layanan): RedirectResponse
    {
        $item = $unitUsaha->layanan()->findOrFail($layanan);

        $item->update(['status_aktif' => false]);

        return back()->with('success', $item->tagihan()->exists()
            ? 'Layanan diarsipkan. Riwayat tagihan warga tetap tersimpan dan katalog baru tidak lagi menampilkan layanan ini.'
            : 'Layanan dinonaktifkan dan tidak lagi tersedia untuk tagihan baru.');
    }

    private function validasiUnit(Request $request): array
    {
        return $request->validate([
            'nama_unit' => ['required', 'string', 'max:100'],
            'penanggung_jawab' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status_aktif' => ['required', 'boolean'],
        ]);
    }

    private function validasiLayanan(Request $request): array
    {
        $data = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:100'],
            'satuan' => ['required_if:jenis_layanan,BULANAN', 'nullable', 'string', 'max:30'],
            'tarif_dasar' => ['required_if:jenis_layanan,BULANAN,SEKALI_BELI', 'nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status_aktif' => ['sometimes', 'boolean'],
            'jenis_layanan' => ['required', 'in:BULANAN,SEKALI_BELI,TAGIHAN_VARIABEL'],
            'tampil_di_katalog' => ['required', 'boolean'],
        ]);

        // The legacy schema keeps these columns NOT NULL. Variable bills do not have a fixed tariff or billing unit.
        if ($data['jenis_layanan'] === 'TAGIHAN_VARIABEL') {
            $data['tarif_dasar'] = 0;
            $data['satuan'] = 'Variabel';
        } elseif ($data['jenis_layanan'] === 'SEKALI_BELI') {
            // Keep legacy NOT NULL column compatible; one-off products have no billing period.
            $data['satuan'] = 'Sekali';
        }

        return $data;
    }

    private function kodeUnitTersedia(string $namaUnit): string
    {
        $dasar = Str::upper(Str::slug($namaUnit, '-'));
        $dasar = substr($dasar !== '' ? $dasar : 'UNIT-USAHA', 0, 13);
        $kode = $dasar;
        $akhiran = 1;

        while (UnitUsaha::where('kode_unit', $kode)->exists()) {
            $suffix = '-'.(++$akhiran);
            $kode = substr($dasar, 0, 20 - strlen($suffix)).$suffix;
        }

        return $kode;
    }
}
