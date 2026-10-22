@props(['status'])

@php
    $colors = match ($status) {
        App\Enums\Status::Done => 'bg-green-100 text-green-900 ring-green-300',
        App\Enums\Status::Partial => 'bg-amber-100 text-amber-900 ring-amber-300',
        App\Enums\Status::NeedsAttention => 'bg-red-100 text-red-900 ring-red-300',
        default => 'bg-stone-200 text-stone-800 ring-stone-300',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-3 py-1 text-base font-semibold whitespace-nowrap ring-1', $colors]) }}>
    {{ $status->label() }}
</span>
