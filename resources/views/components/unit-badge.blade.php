@props(['unitCode'])
@php($unitIcon = match($unitCode) { 'UNIT-AIR' => 'faucet', 'UNIT-SAMPAH' => 'recycle', 'UNIT-SEWA' => 'tent-tree', 'PUSAT' => 'landmark', default => 'building' })
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-lg border border-[#F3C846] bg-[#FDE68A] px-3 py-1 text-xs font-semibold text-[#9A6200] dark:border-[#F5B316]/35 dark:bg-[#F5B316]/20 dark:text-[#FCD34D]']) }}><i data-lucide="{{ $unitIcon }}" stroke-width="1.75" class="h-4 w-4"></i>{{ $slot->isEmpty() ? $unitCode : $slot }}</span>
