<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\UnitUsaha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UnitUsahaController extends Controller
{
    public function index(): View
    {
        $units = UnitUsaha::query()
            ->with('layanan')
            ->withCount('layanan')
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
        ]);
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
        $unitUsaha->layanan()->create($this->validasiLayanan($request));

        return back()->with('success', 'Layanan dan tarif berhasil ditambahkan pada Unit Usaha.');
    }

    public function updateLayanan(Request $request, UnitUsaha $unitUsaha, int $layanan): RedirectResponse
    {
        $item = $unitUsaha->layanan()->findOrFail($layanan);
        $item->update($this->validasiLayanan($request));

        return back()->with('success', 'Layanan dan tarif berhasil diperbarui.');
    }

    public function destroyLayanan(UnitUsaha $unitUsaha, int $layanan): RedirectResponse
    {
        $item = $unitUsaha->layanan()->findOrFail($layanan);

        if ($item->tagihan()->exists()) {
            return back()->withErrors([
                'layanan' => 'Layanan ini sudah tercatat pada tagihan. Layanan tidak dapat dihapus agar riwayat warga tetap utuh.',
            ]);
        }

        $item->delete();

        return back()->with('success', 'Layanan berhasil dihapus.');
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
        return $request->validate([
            'nama_layanan' => ['required', 'string', 'max:100'],
            'satuan' => ['required', 'string', 'max:30'],
            'tarif_dasar' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);
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
