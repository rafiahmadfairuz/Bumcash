@extends('layouts.warga')
@section('title', 'Rincian Tagihan — Bumcash')
@section('content')
<section class="mx-auto max-w-3xl px-4 pb-14 pt-8 sm:px-6 sm:pt-10">
    <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#6B665C] hover:text-[#1C1917]"><i data-lucide="arrow-left" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i> Kembali ke tagihan</a>
    <div class="mt-5"><x-unit-badge :unit-code="$tagihan->layanan->unitUsaha->kode_unit">{{ $tagihan->layanan->unitUsaha->nama_unit }}</x-unit-badge><div class="mt-3 flex flex-wrap items-center justify-between gap-3"><h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Rincian Tagihan</h1><x-payment-status-badge :status="$tagihan->status" /></div><p class="mt-1 text-sm text-[#6B665C]">{{ $tagihan->kode_tagihan }} · {{ $tagihan->layanan->nama_layanan }}</p></div>
    <article class="mt-6 overflow-hidden rounded-2xl border border-[#ECE8DA] bg-white shadow-sm">
        <div class="bg-[#FDE68A] p-5 sm:p-6"><p class="text-xs font-semibold text-[#6B665C]">Jumlah yang perlu dibayar</p><p class="mt-2 text-3xl font-extrabold tracking-tight tabular-nums">Rp {{ number_format((float) $tagihan->nominal, 0, ',', '.') }}</p></div>
        <dl class="divide-y divide-[#ECE8DA] px-5 sm:px-6">
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Unit Usaha</dt><dd class="text-right text-sm font-semibold">{{ $tagihan->layanan->unitUsaha->nama_unit }}</dd></div>
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Jenis layanan</dt><dd class="text-right text-sm font-semibold">{{ $tagihan->layanan->nama_layanan }}</dd></div>
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Periode</dt><dd class="text-right text-sm font-semibold">{{ \Carbon\Carbon::create($tagihan->periode_tahun, $tagihan->periode_bulan, 1)->locale('id')->translatedFormat('F Y') }}</dd></div>
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Rincian pemakaian</dt><dd class="max-w-[60%] text-right text-sm font-semibold">{{ $tagihan->rincian_pemakaian }}</dd></div>
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Jatuh tempo</dt><dd class="text-right text-sm font-semibold">{{ $tagihan->jatuh_tempo->locale('id')->translatedFormat('d F Y') }}</dd></div>
            <div class="flex justify-between gap-4 py-4"><dt class="text-sm text-[#6B665C]">Status</dt><dd><x-payment-status-badge :status="$tagihan->status" /></dd></div>
        </dl>
    </article>

    @if($tagihan->status === 'BELUM_DIBAYAR')
        <div class="mt-5"></div>
        <button type="button" data-modal-open="konfirmasi-pembayaran" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#E09F09] px-6 py-4 text-sm font-bold text-[#1A1302] transition hover:bg-[#C78A05]"><i data-lucide="credit-card" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i> Lanjutkan Pembayaran</button>
        <x-action-modal id="konfirmasi-pembayaran" title="Konfirmasi Pembayaran" subtitle="Pilih metode pembayaran untuk mengirim konfirmasi kepada Pengelola BUMDes.">
            <form method="POST" action="{{ route('warga.tagihan.pembayaran', $tagihan) }}" class="space-y-4">@csrf
                <fieldset><legend class="mb-2 text-sm font-semibold">Metode pembayaran</legend><label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#ECE8DA] p-4 has-[:checked]:border-[#E09F09] has-[:checked]:bg-[#FDE68A]/40 dark:border-[#302C23]"><input type="radio" name="metode_pembayaran" value="TRANSFER_BANK_DESA" required class="mt-1 accent-[#E09F09]"><span><span class="block text-sm font-semibold">Transfer Bank Desa</span><span class="mt-1 block text-xs text-[#6B665C] dark:text-[#9E988A]">Ajukan konfirmasi pembayaran melalui transfer.</span></span><i data-lucide="landmark" stroke-width="1.75" class="h-4 w-4 shrink-0 ml-auto mt-1 text-[#6B665C]"></i></label><label class="mt-2 flex cursor-pointer items-start gap-3 rounded-xl border border-[#ECE8DA] p-4 has-[:checked]:border-[#E09F09] has-[:checked]:bg-[#FDE68A]/40 dark:border-[#302C23]"><input type="radio" name="metode_pembayaran" value="TUNAI_LOKET" required class="mt-1 accent-[#E09F09]"><span><span class="block text-sm font-semibold">Tunai di Loket BUMDes</span><span class="mt-1 block text-xs text-[#6B665C] dark:text-[#9E988A]">Ajukan konfirmasi pembayaran kepada petugas loket.</span></span><i data-lucide="banknote" stroke-width="1.75" class="h-4 w-4 shrink-0 ml-auto mt-1 text-[#6B665C]"></i></label></fieldset>
                @error('metode_pembayaran')<p class="text-sm font-medium text-[#A83838]">{{ $message }}</p>@enderror
                <div class="rounded-xl bg-[#FAF8F1] p-4 dark:bg-[#24211B]"><div class="flex justify-between gap-3 text-sm"><span class="text-[#6B665C] dark:text-[#9E988A]">Nominal tagihan</span><strong>Rp {{ number_format((float) $tagihan->nominal, 0, ',', '.') }}</strong></div><p class="mt-2 text-xs leading-5 text-[#6B665C] dark:text-[#9E988A]">Pengajuan berstatus <strong>Menunggu Konfirmasi</strong>. Kas BUMDes bertambah setelah Pengelola mengonfirmasi pembayaran.</p></div>
                <button class="w-full rounded-lg bg-[#E09F09] px-5 py-3.5 text-sm font-bold text-[#1A1302] hover:bg-[#C78A05]">Kirim Konfirmasi Pembayaran</button>
            </form>
        </x-action-modal>
    @elseif($tagihan->status === 'MENUNGGU_KONFIRMASI')
        <div class="mt-5 rounded-xl border border-[#ECE8DA] bg-[#F7F3E7] p-4 text-sm leading-6 text-[#6B665C] dark:border-[#302C23] dark:bg-[#24211B] dark:text-[#9E988A]"><i data-lucide="hourglass" stroke-width="1.75" class="h-4 w-4 shrink-0 mr-2"></i>Konfirmasi Anda sedang menunggu verifikasi Pengelola BUMDes. Tagihan ini masih tercatat sebagai Piutang Layanan sampai dikonfirmasi.</div>
    @else
        <a href="{{ route('warga.riwayat') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#E09F09] px-6 py-4 text-sm font-bold text-[#1A1302]"><i data-lucide="receipt" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i> Lihat Riwayat & Kuitansi</a>
    @endif
</section>
@endsection
