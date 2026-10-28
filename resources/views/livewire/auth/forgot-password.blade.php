<div class="mx-auto max-w-md px-4 py-12">
    <h1 class="text-2xl font-bold">Lupa password</h1>
    <p class="mt-1 text-sm text-gray-500">Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang password.</p>

    @if ($sent)
        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-6 text-sm text-green-900">
            Jika email tersebut terdaftar, tautan pengaturan ulang password sudah dikirim. Periksa kotak masuk (dan folder spam). Tautan berlaku 60 menit.
        </div>
        <p class="mt-4 text-sm"><a href="{{ route('login') }}" class="font-medium text-blue-700 hover:underline">&larr; Kembali ke halaman masuk</a></p>
    @else
        <form wire:submit="sendLink" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled">
                <span wire:loading.remove>Kirim tautan</span><span wire:loading>Mengirim...</span>
            </button>
            <p class="text-center text-sm text-gray-500"><a href="{{ route('login') }}" class="font-medium text-blue-700 hover:underline">Kembali ke halaman masuk</a></p>
        </form>
    @endif
</div>
