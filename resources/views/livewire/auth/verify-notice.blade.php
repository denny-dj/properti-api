<div class="mx-auto max-w-md px-4 py-12">
    @if ($verified)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-6 text-sm text-green-900">
            <h1 class="text-xl font-bold">Email sudah terverifikasi</h1>
            <p class="mt-2">Anda dapat memesan, chat, dan membayar.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block font-medium text-blue-700 hover:underline">Ke beranda &rarr;</a>
        </div>
    @else
        <h1 class="text-2xl font-bold">Verifikasi email Anda</h1>
        <p class="mt-2 text-sm text-gray-600">
            Kami mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Klik tautan itu untuk mengaktifkan fitur memesan, chat, dan membayar. Tautan berlaku 60 menit.
        </p>

        @if ($sent)
            <p class="mt-4 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">Tautan baru sudah dikirim. Periksa kotak masuk atau folder spam.</p>
        @endif
        @if ($error)
            <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ $error }}</p>
        @endif

        <button type="button" wire:click="resend" wire:loading.attr="disabled" class="mt-5 rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">
            <span wire:loading.remove wire:target="resend">Kirim ulang tautan</span>
            <span wire:loading wire:target="resend">Mengirim...</span>
        </button>
        <p class="mt-4 text-sm text-gray-500">Salah alamat email? Hubungi administrator.</p>
    @endif
</div>
