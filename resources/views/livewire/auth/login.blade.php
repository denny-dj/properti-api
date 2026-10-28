<div class="mx-auto max-w-md px-4 py-12">
    <h1 class="text-2xl font-bold">Masuk</h1>
    <p class="mt-1 text-sm text-gray-500">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-blue-700 hover:underline">Daftar</a></p>

    <form wire:submit="login" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium">Password</label>
            <input id="password" type="password" wire:model="password" autocomplete="current-password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="remember" class="rounded border-gray-300"> Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:underline">Lupa password?</a>
        </div>
        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled">
            <span wire:loading.remove>Masuk</span><span wire:loading>Memproses...</span>
        </button>
    </form>
</div>
