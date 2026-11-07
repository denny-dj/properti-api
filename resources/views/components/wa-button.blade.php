{{--
    Tombol WhatsApp Concierge (form POST, tanpa JavaScript).
    Mencatat prospek (source + reference) lalu membuka WhatsApp admin di tab baru.
    Props: source (property_detail|architect_portfolio|promotion|kpr_calculator), reference (id item), label, variant (card|solid|outline|light), size (sm|md|lg)
--}}
@props(['source', 'reference' => null, 'label' => 'Konsultasi via Admin', 'variant' => 'solid', 'size' => 'md'])
@php
    $sizes = ['sm' => 'px-3.5 py-2.5 text-sm', 'md' => 'px-5 py-3 text-sm', 'lg' => 'px-6 py-3.5 text-base'];
    $variants = [
        'solid' => 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25 hover:bg-emerald-600',
        'outline' => 'border border-emerald-600 bg-white text-emerald-700 hover:bg-emerald-50',
        'light' => 'bg-white text-emerald-700 hover:bg-emerald-50',
    ];
    // Varian kartu: CTA standar di kartu properti & portofolio
    $card = 'flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition-colors hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2';
    $buttonClass = $variant === 'card'
        ? $card
        : 'inline-flex w-full items-center justify-center gap-2 rounded-xl font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['solid']);
@endphp
<form method="POST" action="{{ route('concierge.redirect') }}" target="_blank" {{ $attributes->only('class') }}>
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    @if ($reference)
        <input type="hidden" name="reference_id" value="{{ $reference }}">
    @endif
    <button type="submit" class="{{ $buttonClass }}">
        <x-icon name="whatsapp" class="h-5 w-5 shrink-0" />
        <span>{{ $label }}</span>
    </button>
</form>
