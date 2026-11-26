@extends('layouts.app')
@section('title', 'Dashboard Keuangan BUMDes — Bumcash')
@section('content')
<section class="mx-auto w-full min-w-0 max-w-[1600px] pb-8">
    <div class="mb-5 flex min-w-0 flex-col justify-between gap-4 xl:flex-row xl:items-end">
        <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-[.14em] text-[#6B665C]">Ringkasan Pengelolaan</p><h2 class="page-title mt-1 text-2xl font-bold tracking-tight text-[#1C1917] dark:text-[#F5F2EA]">Selamat datang, Pengelola BUMDes</h2><p class="mt-1 text-sm text-[#6B665C] dark:text-[#9E988A]">Pantau saldo kas, arus keuangan, dan layanan warga.</p></div>
        <form method="GET" action="{{ route('pengelola.dashboard') }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-[#ECE8DA] bg-white px-3 py-2 text-sm text-[#6B665C]">
            <i data-lucide="calendar-days" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i><label class="sr-only" for="tanggal-mulai">Tanggal mulai</label><input id="tanggal-mulai" type="date" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] ?? $tanggalMulai->toDateString() }}" class="w-[8.5rem] border-0 bg-transparent p-0 text-xs text-[#6B665C] focus:outline-none focus:ring-0"><span class="text-[#8A8275]">—</span><label class="sr-only" for="tanggal-akhir">Tanggal akhir</label><input id="tanggal-akhir" type="date" name="tanggal_akhir" value="{{ $filter['tanggal_akhir'] ?? $tanggalAkhir->toDateString() }}" class="w-[8.5rem] border-0 bg-transparent p-0 text-xs text-[#6B665C] focus:outline-none focus:ring-0">
            @if(!empty($filter['unit_usaha_id']))<input type="hidden" name="unit_usaha_id" value="{{ $filter['unit_usaha_id'] }}">@endif
            <button class="grid h-8 w-8 place-items-center rounded-lg bg-[#FDE68A] text-[#1C1917] transition hover:bg-[#FBD34D]" aria-label="Terapkan periode"><i data-lucide="list-filter" stroke-width="1.75" class="h-4 w-4 shrink-0 text-xs"></i></button>
        </form>
    </div>

    <div class="grid min-w-0 grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="min-w-0 space-y-5 xl:col-span-8">
            <div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-kpi-card title="Total Pemasukan" :amount="'Rp '.number_format($pemasukan_periode,0,',','.')" icon-class="trending-up" comparison="Dari periode sebelumnya" :trend-percent="$perubahan_pemasukan" :trend-positive="$perubahan_pemasukan >= 0" />
                <x-stat-kpi-card title="Total Pengeluaran" :amount="'Rp '.number_format($pengeluaran_periode,0,',','.')" icon-class="trending-down" comparison="Dari periode sebelumnya" :trend-percent="$perubahan_pengeluaran" :trend-positive="$perubahan_pengeluaran <= 0" />
                <x-stat-kpi-card title="Saldo Kas Bersih" :amount="'Rp '.number_format($saldo_kas,0,',','.')" icon-class="wallet" comparison="Dari periode sebelumnya" :trend-percent="$perubahan_saldo" :trend-positive="$perubahan_saldo >= 0" />
            </div>

            <article class="min-w-0 overflow-hidden rounded-2xl border border-[#ECE8DA] bg-white p-4 sm:p-6">
                <div class="flex min-w-0 flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0"><div class="flex min-w-0 flex-wrap items-center justify-between gap-x-3 gap-y-2"><h3 class="text-base font-semibold text-[#1C1917] dark:text-[#F5F2EA]">Ringkasan Keuangan BUMDes</h3><a href="{{ route('pengelola.unit-usaha.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#ECE8DA] px-3 py-2 text-xs font-semibold leading-none text-[#6B665C] transition hover:bg-[#FDE68A] dark:border-[#302C23] dark:text-[#D4CDBF] dark:hover:bg-[#F5B316]/20"><i data-lucide="plus" stroke-width="1.75" class="h-3.5 w-3.5 shrink-0"></i>Kelola Unit</a></div><p class="mt-1 text-xs text-[#6B665C] dark:text-[#9E988A]">Pemasukan dan pengeluaran · enam bulan hingga {{ $tanggalAkhir->locale('id')->translatedFormat('F Y') }}</p><p class="mt-3 text-2xl font-bold tabular-nums tracking-tight text-[#1C1917] dark:text-[#F5F2EA]">Rp {{ number_format($pemasukan_periode,0,',','.') }}</p><span class="mt-2 inline-flex rounded-full bg-[#FDE68A] px-2 py-0.5 text-[clamp(.6875rem,.72vw,.75rem)] sm:text-[11px] lg:text-xs font-semibold text-[#624300]">Pemasukan periode berjalan</span></div>
                    <div class="flex shrink-0 flex-wrap items-center gap-3 sm:flex-col sm:items-end"><label class="sr-only" for="filter-unit-chart">Filter unit usaha</label><select id="filter-unit-chart" onchange="const u=this.value;const q=new URLSearchParams(window.location.search);u?q.set('unit_usaha_id',u):q.delete('unit_usaha_id');window.location.search=q.toString()" class="max-w-44 rounded-lg border border-[#ECE8DA] bg-white px-3 py-1.5 text-xs text-[#6B665C]"><option value="">Semua Unit</option>@foreach($unitUsaha as $unit)<option value="{{ $unit->id }}" @selected(($filter['unit_usaha_id'] ?? '') == $unit->id)>{{ $unit->nama_unit }}</option>@endforeach</select><div class="flex flex-wrap items-center gap-3 text-[clamp(.6875rem,.72vw,.75rem)] sm:text-[11px] lg:text-xs text-[#6B665C]"><span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-[#2F7D4A]"></span>Layanan warga</span><span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-[#E09F09]"></span>Pemasukan manual</span><span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-[#A83838]"></span>Pengeluaran</span></div></div>
                </div>
                <div class="mt-4 w-full min-w-0 overflow-hidden">
                    <div id="cashflow-chart" class="h-[250px] w-full min-w-0" aria-label="Grafik pemasukan dan pengeluaran enam bulan"></div>
                </div>
                <div id="cashflow-detail" class="mt-4 rounded-xl border border-[#ECE8DA] bg-[#FAF8F1] p-3 sm:p-4" aria-live="polite">
                    <div class="flex items-center justify-between gap-3">
                        <p id="cashflow-detail-period" class="text-xs font-semibold text-[#1C1917]">Klik batang untuk melihat rincian bulan</p>
                        <div class="flex shrink-0 gap-3 text-xs tabular-nums">
                            <span class="text-[#2F7D4A]">Masuk <strong id="cashflow-detail-in">Rp 0</strong></span>
                            <span class="text-[#A83838]">Keluar <strong id="cashflow-detail-out">Rp 0</strong></span>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <section class="min-w-0">
                            <h4 class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-[#2F7D4A]">Pemasukan</h4>
                            <div id="cashflow-detail-income" class="space-y-2"></div>
                        </section>
                        <section class="min-w-0">
                            <h4 class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-[#A83838]">Pengeluaran</h4>
                            <div id="cashflow-detail-expense" class="space-y-2"></div>
                        </section>
                    </div>
                </div>
            </article>

            <article class="min-w-0 overflow-hidden rounded-2xl border border-[#ECE8DA] bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5"><div><h3 class="text-base font-semibold text-[#1C1917]">Riwayat Transaksi Terbaru</h3><p class="mt-1 text-xs text-[#6B665C]">Jurnal kas dan pengajuan pembayaran warga</p></div><div class="flex gap-2"><span class="inline-flex items-center rounded-lg border border-[#ECE8DA] bg-white px-3 py-1.5 text-xs text-[#6B665C]">Periode terpilih</span><a href="{{ route('pengelola.arus-kas', array_filter(['unit_usaha_id'=>$filter['unit_usaha_id'] ?? null,'tanggal_mulai'=>$tanggalMulai->toDateString(),'tanggal_akhir'=>$tanggalAkhir->toDateString()])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#ECE8DA] px-3 py-1.5 text-xs font-medium text-[#6B665C] transition hover:bg-[#FDE68A]"><i data-lucide="list-filter" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i>Filter</a></div></div>
                <div class="w-full min-w-0 overflow-x-auto"><table class="w-full min-w-[780px] text-left text-sm"><thead class="border-y border-[#ECE8DA] bg-[#FAF8F1] text-xs font-medium text-[#6B665C]"><tr><th class="px-5 py-3">Nama Transaksi & Unit</th><th class="px-4 py-3">Tanggal & Waktu</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3">Keterangan</th><th class="px-5 py-3 text-right">Status</th></tr></thead><tbody class="divide-y divide-[#ECE8DA]">
                    @forelse($aktivitasTerbaru as $aktivitas)
                        @php($statusStyle = match($aktivitas['status']) { 'LUNAS'=>['bg-[#FDE68A] text-[#624300]','Lunas'], 'MENUNGGU'=>['bg-[#F7F3E7] text-[#6B665C]','Menunggu'], 'KELUAR'=>['bg-[#FCEAEA] text-[#A83838]','Keluar'], default=>['bg-[#FDE68A] text-[#624300]','Masuk'] })
                        <tr class="text-[#1C1917]"><td class="max-w-[250px] px-5 py-3.5"><p class="truncate text-xs font-semibold" title="{{ $aktivitas['aktivitas'] }}">{{ $aktivitas['aktivitas'] }}</p><p class="mt-1 truncate text-[clamp(.6875rem,.72vw,.75rem)] sm:text-[11px] lg:text-xs text-[#6B665C]" title="{{ $aktivitas['unit'] }}">{{ $aktivitas['unit'] }} · {{ $aktivitas['kode'] }}</p></td><td class="px-4 py-3.5 text-xs text-[#6B665C]"><span class="block whitespace-nowrap">{{ $aktivitas['tanggal']->locale('id')->translatedFormat('d M Y') }}</span><span class="mt-1 block text-[clamp(.6875rem,.72vw,.75rem)] sm:text-[11px] lg:text-xs">{{ $aktivitas['waktu']?->format('H:i') ?? '—' }} WIB</span></td><td class="px-4 py-3.5 text-right text-xs font-semibold tabular-nums whitespace-nowrap {{ $aktivitas['jenis']==='PENGELUARAN' ? 'text-[#A83838]' : 'text-[#1C1917]' }}">{{ $aktivitas['jenis']==='PENGELUARAN' ? '− ' : '' }}Rp {{ number_format($aktivitas['nominal'],0,',','.') }}</td><td class="max-w-[180px] px-4 py-3.5 text-xs text-[#6B665C]">{{ $aktivitas['keterangan'] }}</td><td class="px-5 py-3.5 text-right"><span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-semibold {{ $statusStyle[0] }}">{{ $statusStyle[1] }}</span></td></tr>
                    @empty<tr><td colspan="5" class="px-5 py-10 text-center text-sm text-[#6B665C]">Belum ada transaksi pada periode ini.</td></tr>@endforelse
                </tbody></table></div>
            </article>
        </div>

        <aside class="grid min-w-0 grid-cols-1 gap-5 md:grid-cols-2 xl:col-span-4 xl:grid-cols-1 xl:content-start">
            <section class="min-w-0 rounded-2xl border border-[#ECE8DA] bg-white p-4 sm:p-5"><div class="flex items-center justify-between gap-2"><div class="flex items-center gap-2.5"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="wallet" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span><h3 class="text-sm font-semibold text-[#1C1917]">Kas Utama BUMDes</h3></div><a href="{{ route('pengelola.arus-kas') }}" class="inline-flex items-center gap-1 rounded-lg bg-[#FDE68A] px-3 py-1.5 text-xs font-semibold text-[#1C1917] transition hover:bg-[#FBD34D]"><i data-lucide="plus" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i>Catat Kas</a></div>
                <div class="relative mt-4 overflow-hidden rounded-2xl border border-[#EAB308]/50 bg-gradient-to-br from-[#FDE68A] via-[#FCD34D] to-[#F59E0B]/85 p-3.5 sm:p-4 lg:p-5 xl:p-6 text-[#1A1302] dark:border-[#785B1A] dark:from-[#261E0D] dark:via-[#332710] dark:to-[#423212]"><div class="pointer-events-none absolute -right-6 -top-8 h-24 w-24 rounded-full bg-[#D99B06]/15 blur-xl dark:bg-[#F5B316]/10"></div><p class="relative text-xs font-medium text-[#453206] dark:text-[#E5C97B]">Rekening kas internal BUMDes</p><p class="relative mt-1 text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs text-[#453206] dark:text-[#E5C97B] sm:text-[11px] lg:text-xs">Saldo Tersedia</p><p class="relative mt-1 min-w-0 break-words text-[clamp(1.25rem,2.3vw,1.875rem)] font-extrabold leading-tight tracking-tight tabular-nums text-[#1C1402] dark:text-[#FFF8E7] sm:text-2xl lg:text-[26px] xl:text-3xl">Rp {{ number_format($saldo_kas,0,',','.') }}</p><div class="relative mt-4 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 border-t border-[#D99B06]/30 pt-3 text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs text-[#594108] dark:border-[#785B1A]/60 dark:text-[#D4B86A] sm:mt-5 sm:text-[11px] lg:text-xs"><span>{{ $unitUsaha->count() }} Unit Aktif</span><span>{{ $tanggalAkhir->locale('id')->translatedFormat('F Y') }}</span></div></div>
                <div class="mt-4 grid grid-cols-4 gap-2.5"><a href="{{ route('pengelola.arus-kas') }}#form-transaksi" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl border border-[#ECE8DA] bg-[#FAF8F1] px-1 py-2.5 text-[#1C1917] transition hover:bg-[#FDE68A]"><span class="grid h-9 w-9 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="arrow-down" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span><span class="text-center text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-medium sm:text-[11px]">Pemasukan</span></a><a href="{{ route('pengelola.arus-kas') }}#form-transaksi" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl border border-[#ECE8DA] bg-[#FAF8F1] px-1 py-2.5 text-[#1C1917] transition hover:bg-[#FDE68A]"><span class="grid h-9 w-9 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="arrow-up" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span><span class="text-center text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-medium sm:text-[11px]">Pengeluaran</span></a><a href="{{ route('pengelola.verifikasi') }}" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl border border-[#ECE8DA] bg-[#FAF8F1] px-1 py-2.5 text-[#1C1917] transition hover:bg-[#FDE68A]"><span class="grid h-9 w-9 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="list-checks" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span><span class="text-center text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-medium sm:text-[11px]">Verifikasi</span></a><a href="{{ route('pengelola.laporan-musdes') }}" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl border border-[#ECE8DA] bg-[#FAF8F1] px-1 py-2.5 text-[#1C1917] transition hover:bg-[#FDE68A]"><span class="grid h-9 w-9 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="file-text" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span><span class="text-center text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-medium sm:text-[11px]">Laporan</span></a></div>
            </section>

            <section class="min-w-0 rounded-2xl border border-[#ECE8DA] bg-white p-4 sm:p-5"><div class="flex items-start justify-between gap-3"><div><h3 class="text-sm font-semibold text-[#1C1917]">Realisasi Tagihan Bulan Ini</h3><p class="mt-1 text-xs text-[#6B665C]">Rp {{ number_format($nominal_tagihan_lunas,0,',','.') }} terkumpul dari Rp {{ number_format($nominal_tagihan_terbit,0,',','.') }}</p></div><span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl border border-[#ECE8DA] bg-[#FDE68A] text-[#9A6200]"><i data-lucide="chart-pie" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></span></div><div class="mt-4 flex items-center gap-3"><div class="h-2 min-w-0 flex-1 overflow-hidden rounded-full bg-[#ECE8DA]"><div class="h-full rounded-full bg-[#E09F09] transition-all" style="width: {{ min(100,max(0,$collection_rate)) }}%"></div></div><strong class="shrink-0 text-xs text-[#1C1917]">{{ number_format($collection_rate,1,',','.') }}%</strong></div><div class="mt-4 flex items-center justify-between border-t border-[#ECE8DA] pt-3 text-xs"><span class="text-[#6B665C]">Piutang warga</span><strong class="tabular-nums text-[#1C1917]">Rp {{ number_format($total_piutang,0,',','.') }}</strong></div><a href="{{ route('pengelola.piutang') }}" class="mt-3 inline-flex text-xs font-semibold text-[#9A6200] hover:underline">Lihat daftar piutang <i data-lucide="arrow-right" stroke-width="1.75" class="h-4 w-4 shrink-0 ml-1 mt-0.5"></i></a></section>

            <section class="min-w-0 rounded-2xl border border-[#ECE8DA] bg-white p-4 sm:p-5"><div class="flex items-center justify-between gap-3"><div><h3 class="text-sm font-semibold text-[#1C1917]">Aktivitas Warga Terbaru</h3><p class="mt-1 text-xs text-[#6B665C]">Pembayaran dan pencatatan kas</p></div><span class="rounded-lg bg-[#F7F3E7] px-2.5 py-1 text-[clamp(.625rem,.68vw,.75rem)] sm:text-[11px] lg:text-xs font-semibold text-[#6B665C]">{{ $tanggalAkhir->isToday() ? 'Hari Ini' : $tanggalAkhir->locale('id')->translatedFormat('d M') }}</span></div><div class="mt-4 space-y-3">@forelse($aktivitasTerbaru->take(4) as $aktivitas)<div class="flex min-w-0 items-center gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#FDE68A] text-xs text-[#9A6200]"><i data-lucide="{{ $aktivitas['jenis']==='PENGELUARAN' ? 'credit-card' : 'user-round' }}" stroke-width="1.75" class="h-4 w-4"></i></span><div class="min-w-0 flex-1"><p class="truncate text-xs text-[#1C1917]"><strong>{{ $aktivitas['status']==='MENUNGGU' ? 'Pengajuan warga' : $aktivitas['aktivitas'] }}</strong> · {{ $aktivitas['unit'] }}</p><p class="mt-1 text-[clamp(.6875rem,.72vw,.75rem)] sm:text-[11px] lg:text-xs text-[#6B665C]">{{ $aktivitas['waktu']?->locale('id')->translatedFormat('d M · H:i') ?? $aktivitas['tanggal']->locale('id')->translatedFormat('d M') }} WIB · Rp {{ number_format($aktivitas['nominal'],0,',','.') }}</p></div></div>@empty<p class="py-5 text-center text-xs text-[#6B665C]">Belum ada aktivitas pada periode ini.</p>@endforelse</div></section>
        </aside>
    </div>
</section>
<script>
const formatRupiahLengkap = (nilai) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(nilai);
const formatRupiahRingkas = (nilai) => { const angka = Math.abs(nilai); if (angka >= 10000000) return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(nilai / 1000000) + 'jt'; if (angka >= 1000000) return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(nilai / 1000000) + 'jt'; if (angka >= 1000) return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(nilai / 1000) + 'rb'; return formatRupiahLengkap(nilai); };
const cashflowNode = document.querySelector('#cashflow-chart');
const chartIsDark = document.documentElement.classList.contains('dark');
let cashflowChart = null;
if (cashflowNode && window.ApexCharts) {
cashflowChart = new ApexCharts(cashflowNode, {
    chart:{type:'bar',height:250,width:'100%',stacked:true,toolbar:{show:false},fontFamily:'Plus Jakarta Sans, sans-serif',parentHeightOffset:0,background:'transparent',foreColor:chartIsDark?'#9E988A':'#6B665C',events:{
        dataPointSelection: function(_event, _chart, config) {
            if (typeof tampilkanRincianPemasukan === 'function') {
                tampilkanRincianPemasukan(config.dataPointIndex);
            }
        }
    }},theme:{mode:chartIsDark?'dark':'light'},
    series:[
        {name:'Layanan warga',data:@json($tren_layanan_warga)},
        {name:'Pemasukan manual',data:@json($tren_pemasukan_manual)},
        {name:'Pengeluaran',data:@json($tren_pengeluaran)}
    ],
    colors:[chartIsDark?'#86C69A':'#2F7D4A',chartIsDark?'#F5B316':'#E09F09',chartIsDark?'#D58A8A':'#A83838'],plotOptions:{bar:{horizontal:false,columnWidth:'42%',borderRadius:5,borderRadiusApplication:'end'}},dataLabels:{enabled:false},
    xaxis:{categories:@json($label_bulan),axisBorder:{show:false},axisTicks:{show:false},labels:{trim:true,hideOverlappingLabels:true,style:{colors:chartIsDark?'#9E988A':'#6B665C',fontSize:'11px'}}},
    yaxis:{min:0,forceNiceScale:true,labels:{formatter:formatRupiahRingkas,style:{colors:chartIsDark?'#9E988A':'#6B665C',fontSize:'10px'}}},
    grid:{borderColor:chartIsDark?'#302C23':'#ECE8DA',strokeDashArray:4,xaxis:{lines:{show:false}}},legend:{show:false},
    tooltip:{theme:chartIsDark?'dark':'light',shared:false,intersect:true,y:{formatter:formatRupiahLengkap}},states:{hover:{filter:{type:'lighten',value:.08}},active:{filter:{type:'none'}}}
});
cashflowChart.render();
window.addEventListener('bumcash:theme-change', (event) => {
    const dark = Boolean(event.detail?.dark);
    cashflowChart.updateOptions({ theme:{mode:dark?'dark':'light'}, chart:{foreColor:dark?'#9E988A':'#6B665C'}, colors:[dark?'#86C69A':'#2F7D4A',dark?'#F5B316':'#E09F09',dark?'#D58A8A':'#A83838'], xaxis:{labels:{style:{colors:dark?'#9E988A':'#6B665C'}}}, yaxis:{labels:{style:{colors:dark?'#9E988A':'#6B665C'}}}, grid:{borderColor:dark?'#302C23':'#ECE8DA'}, tooltip:{theme:dark?'dark':'light'} });
});
}

const rincianPemasukan = @json($rincian_pemasukan);
const rincianPengeluaran = @json($rincian_pengeluaran);
const labelBulanChart = @json($label_bulan);
const layananWargaPerBulan = @json($tren_layanan_warga);
const pemasukanManualPerBulan = @json($tren_pemasukan_manual);
const pengeluaranPerBulan = @json($tren_pengeluaran);
const pemasukanTotalPerBulan = layananWargaPerBulan.map((nilai, index) => Number(nilai) + Number(pemasukanManualPerBulan[index]));

function tampilkanRincianPemasukan(index) {
    const periode = labelBulanChart[index] || '';
    const pemasukan = rincianPemasukan[index] || [];
    const pengeluaran = rincianPengeluaran[index] || [];
    const periodeNode = document.querySelector('#cashflow-detail-period');
    const inNode = document.querySelector('#cashflow-detail-in');
    const outNode = document.querySelector('#cashflow-detail-out');
    const incomeNode = document.querySelector('#cashflow-detail-income');
    const expenseNode = document.querySelector('#cashflow-detail-expense');
    if (!periodeNode || !inNode || !outNode || !incomeNode || !expenseNode) return;

    periodeNode.textContent = periode;
    inNode.textContent = formatRupiahLengkap(pemasukanTotalPerBulan[index] || 0);
    outNode.textContent = formatRupiahLengkap(pengeluaranPerBulan[index] || 0);

    const escapeHtml = (nilai) => String(nilai).replace(/[&<>"']/g, (karakter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[karakter]);
    const renderItem = (item, color) => '<div class="flex items-start gap-3 rounded-lg border border-[#ECE8DA] bg-white px-3 py-2.5">'
            + '<span class="mt-1.5 h-2 w-2 shrink-0 rounded-sm ' + color + '"></span>'
            + '<div class="min-w-0 flex-1"><p class="text-xs font-semibold text-[#1C1917]">' + escapeHtml(item.unit) + ' · ' + escapeHtml(item.kategori) + '</p>'
            + '<p class="mt-1 text-[11px] text-[#6B665C]">' + escapeHtml(item.sumber) + ' · ' + escapeHtml(item.tanggal) + '</p></div>'
            + '<strong class="shrink-0 text-xs tabular-nums text-[#1C1917]">' + formatRupiahLengkap(item.nominal) + '</strong></div>';

    incomeNode.innerHTML = pemasukan.length
        ? pemasukan.map((item) => renderItem(item, item.sumber === 'Pembayaran layanan warga' ? 'bg-[#2F7D4A]' : 'bg-[#E09F09]')).join('')
        : '<p class="rounded-lg border border-dashed border-[#ECE8DA] px-3 py-5 text-center text-xs text-[#6B665C]">Tidak ada pemasukan.</p>';
    expenseNode.innerHTML = pengeluaran.length
        ? pengeluaran.map((item) => renderItem(item, 'bg-[#A83838]')).join('')
        : '<p class="rounded-lg border border-dashed border-[#ECE8DA] px-3 py-5 text-center text-xs text-[#6B665C]">Tidak ada pengeluaran.</p>';
}
</script>
@endsection
