@props(['title', 'subtitle' => null, 'isOpen' => false, 'onClose' => null, 'id' => 'action-modal'])
<dialog id="{{ $id }}" {{ $attributes->class(['w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-[#ECE8DA] bg-white p-0 text-[#1C1917] shadow-2xl backdrop:bg-[#1C1917]/50 dark:border-[#302C23] dark:bg-[#1B1915] dark:text-[#F5F2EA]']) }} @if($isOpen) open @endif>
    <div class="p-3.5 sm:p-4 lg:p-5 xl:p-6"><div class="flex min-w-0 items-start justify-between gap-4"><div class="min-w-0"><h2 class="text-lg font-bold">{{ $title }}</h2>@if($subtitle)<p class="mt-1 text-sm text-[#6B665C] dark:text-[#9E988A]">{{ $subtitle }}</p>@endif</div><button type="button" class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#FDE68A] text-[#1A1302] dark:bg-[#F5B316]/20 dark:text-[#FCD34D]" data-modal-close="{{ $id }}" aria-label="Tutup"><i data-lucide="x" stroke-width="1.75" class="h-4 w-4 shrink-0 "></i></button></div><div class="mt-5">{{ $slot }}</div></div>
</dialog>
@once
<script>
document.addEventListener('click', function (event) {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) document.getElementById(opener.dataset.modalOpen)?.showModal();
    const closer = event.target.closest('[data-modal-close]');
    if (closer) document.getElementById(closer.dataset.modalClose)?.close();
});
</script>
@endonce
