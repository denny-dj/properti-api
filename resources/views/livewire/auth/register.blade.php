<div class="mx-auto max-w-md px-4 py-12">
    <h1 class="text-2xl font-bold">Buat akun</h1>
    <p class="mt-1 text-sm text-gray-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-blue-700 hover:underline">Masuk</a></p>

    <form wire:submit="register" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @foreach ([
            ['name', 'Nama lengkap', 'text', 'name'],
            ['email', 'Email', 'email', 'email'],
            ['phone', 'Nomor HP', 'tel', 'tel'],
            ['password', 'Password', 'password', 'new-password'],
            ['password_confirmation', 'Ulangi password', 'password', 'new-password'],
        ] as [$field, $label, $type, $auto])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium">{{ $label }}</label>
                <input id="{{ $field }}" type="{{ $type }}" wire:model="{{ $field }}" autocomplete="{{ $auto }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <p class="text-xs text-gray-500">Password minimal 8 karakter, kombinasi huruf dan angka.</p>
        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled">
            <span wire:loading.remove>Daftar</span><span wire:loading>Memproses...</span>
        </button>
        <p class="text-center text-sm text-gray-500">Developer, agen, atau arsitek? <a href="{{ route('register.partner') }}" class="font-medium text-blue-700 hover:underline">Daftar sebagai mitra</a></p>
    </form>
</div>
