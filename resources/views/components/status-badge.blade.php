@props(['status', 'context' => 'booking'])
@php
    $labels = [
        'booking' => [
            'pending' => ['Menunggu konfirmasi', 'bg-amber-100 text-amber-800'],
            'confirmed' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
            'rejected' => ['Ditolak', 'bg-red-100 text-red-800'],
            'cancelled' => ['Dibatalkan', 'bg-gray-100 text-gray-700'],
            'completed' => ['Selesai', 'bg-green-100 text-green-800'],
        ],
        'transaction' => [
            'pending' => ['Menunggu pembayaran', 'bg-amber-100 text-amber-800'],
            'paid' => ['Lunas', 'bg-green-100 text-green-800'],
            'failed' => ['Gagal', 'bg-red-100 text-red-800'],
            'expired' => ['Kedaluwarsa', 'bg-gray-100 text-gray-700'],
            'cancelled' => ['Dibatalkan', 'bg-gray-100 text-gray-700'],
            'refunded' => ['Dikembalikan', 'bg-purple-100 text-purple-800'],
        ],
    ];
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    [$text, $classes] = $labels[$context][$value] ?? [ucfirst($value), 'bg-gray-100 text-gray-700'];
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $classes }}">{{ $text }}</span>
