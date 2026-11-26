<!doctype html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Bumcash</title>
    <script>try{document.documentElement.className=localStorage.getItem('bumcash_theme')==='dark'?'dark':'light'}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{sans:['Plus Jakarta Sans','sans-serif']}}}}</script><script src="https://unpkg.com/lucide@latest"></script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif}html.light body,html.light .auth-panel{background:#FAF8F1;color:#1C1917}html.dark body,html.dark .auth-panel{background:#12110E;color:#F5F2EA}html.light .auth-card{background:#fff;border-color:#ECE8DA;color:#1C1917}html.dark .auth-card{background:#1B1915;border-color:#2D2A22;color:#F5F2EA}html.light .auth-input{background:#FAF8F1;border-color:#E2DDD0;color:#1C1917}html.dark .auth-input{background:#24211B;border-color:#363126;color:#F5F2EA}html.dark .auth-muted{color:#9E988A}html.light .auth-muted{color:#6B665C}html.dark .auth-label{color:#F5F2EA}html.light .auth-label{color:#1C1917}.password-rule{display:flex;align-items:center;gap:.5rem;transition:color .22s ease,transform .22s ease}.password-rule .rule-icon{display:inline-grid;width:1rem;height:1rem;place-items:center;border-radius:999px;background:#e7e5e4;color:#78716c;font-size:.65rem;transition:transform .22s ease,background-color .22s ease,color .22s ease}.password-rule.is-valid{color:#16803c;transform:translateX(2px)}.password-rule.is-valid .rule-icon{background:#dcfce7;color:#16803c;transform:scale(1.08)}.password-rule.is-invalid{color:#a16207}.password-rule.is-invalid .rule-icon{background:#fef3c7;color:#a16207}#password-rules.rules-complete{animation:rules-ready .42s ease-out}@keyframes rules-ready{0%{box-shadow:0 0 0 0 rgba(22,128,60,0)}45%{box-shadow:0 0 0 3px rgba(22,128,60,.22)}100%{box-shadow:0 0 0 0 rgba(22,128,60,0)}}@media(prefers-reduced-motion:reduce){.password-rule,.password-rule .rule-icon,#password-progress{transition:none!important}#password-rules.rules-complete{animation:none!important}}</style>
</head>
<body class="min-h-screen antialiased">
<main class="grid min-h-screen lg:grid-cols-2">
    <section class="relative hidden min-h-screen flex-col justify-between overflow-hidden border-r border-amber-200/80 bg-gradient-to-br from-amber-50 via-amber-100 to-amber-200/90 p-12 dark:border-[#362E1C] dark:from-[#18150E] dark:via-[#241E12] dark:to-[#302714] lg:flex">
        <img src="{{ asset('logo_besar.png') }}" alt="Bumcash" class="h-9 w-auto self-start object-contain dark:hidden">
        <img src="{{ asset('logo_besar_dark.png') }}" alt="Bumcash" class="hidden h-9 w-auto self-start object-contain dark:block">
        <div class="max-w-xl"><p class="mb-4 inline-flex items-center gap-2 rounded-xl border border-amber-300/50 bg-white/60 px-3 py-1.5 text-xs font-semibold text-stone-700 dark:border-amber-400/30 dark:bg-white/5 dark:text-stone-300"><i data-lucide="landmark" class="h-4 w-4"></i>Keuangan desa yang mudah dipantau</p><h1 class="text-3xl font-extrabold leading-tight text-stone-900 dark:text-amber-50 xl:text-4xl">Transparansi Keuangan BUMDes & Pelayanan Warga dalam Satu Sistem Terpadu</h1><p class="mt-4 max-w-lg text-sm leading-6 text-stone-700 dark:text-stone-300">Kelola transaksi tiap Unit Usaha, pantau tagihan layanan, dan siapkan laporan Musdes dari satu tempat.</p><div class="mt-8 grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3"><article class="rounded-2xl border border-amber-200/80 bg-white/80 p-4 text-sm font-semibold dark:border-[#3D341E] dark:bg-[#262116]/90">Pembukuan Multi-Unit Usaha</article><article class="rounded-2xl border border-amber-200/80 bg-white/80 p-4 text-sm font-semibold dark:border-[#3D341E] dark:bg-[#262116]/90">Layanan Tagihan & Kuitansi Warga</article><article class="rounded-2xl border border-amber-200/80 bg-white/80 p-4 text-sm font-semibold dark:border-[#3D341E] dark:bg-[#262116]/90">Laporan Musdes</article></div></div>
        <p class="text-xs text-stone-600 dark:text-stone-400">© {{ now()->year }} Bumcash — Sistem Informasi Keuangan BUMDes</p>
    </section>
    <section class="auth-panel relative flex min-h-screen items-center justify-center p-6 sm:p-12">
        <button id="theme-toggle" type="button" aria-label="Ganti tema" class="absolute right-5 top-5 rounded-xl border border-[#ECE8DA] bg-white p-3 text-[#6B665C] hover:bg-amber-100 dark:border-[#2D2A22] dark:bg-[#1B1915] dark:text-stone-300"><i data-lucide="moon" class="h-4 w-4"></i></button>
        <div class="w-full max-w-md"><img src="{{ asset('logo_besar.png') }}" alt="Bumcash" class="mx-auto mb-6 h-8 w-auto dark:hidden lg:hidden"><img src="{{ asset('logo_besar_dark.png') }}" alt="Bumcash" class="mx-auto mb-6 hidden h-8 w-auto dark:block lg:hidden">
            <div class="auth-card rounded-2xl border p-6 shadow-sm sm:p-8"><header class="mb-7"><h2 class="text-2xl font-bold tracking-tight">{{ $heading }}</h2><p class="auth-muted mt-2 text-sm leading-6">{{ $description }}</p></header>
                @if(session('status'))<div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>@endif
                @if($errors->any())<div role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                @include('auth.partials.forms.' . $content)
            </div>
        </div>
    </section>
</main>
<script>if(window.lucide)lucide.createIcons();const b=document.getElementById('theme-toggle');b?.addEventListener('click',()=>{let d=document.documentElement.classList.toggle('dark');document.documentElement.classList.toggle('light',!d);try{localStorage.setItem('bumcash_theme',d?'dark':'light')}catch(e){}if(window.lucide)lucide.createIcons()})</script>
</body></html>
