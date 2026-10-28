<div class="mx-auto max-w-md px-4 py-12">
    <h1 class="text-2xl font-bold">Atur ulang password</h1>
    <p class="mt-1 text-sm text-gray-500">Buat password baru untuk akun Anda.</p>

    <form wire:submit="resetPassword" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium">Password baru</label>
            <input id="password" type="password" wire:model="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium">Ulangi password baru</label>
            <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <p class="text-xs text-gray-500">Minimal 8 karakter, kombinasi huruf dan angka.</p>
        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled">
            <span wire:loading.remove>Simpan password baru</span><span wire:loading>Memproses...</span>
        </button>
    </form>
</div>
