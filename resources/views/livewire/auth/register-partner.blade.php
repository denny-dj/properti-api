<div class="mx-auto max-w-2xl px-4 py-12">
    <h1 class="text-2xl font-bold">Daftar sebagai mitra</h1>
    <p class="mt-1 text-sm text-gray-500">Akun mitra akan diverifikasi administrator sebelum dapat memposting properti atau layanan.</p>

    <form wire:submit="register" class="mt-6 space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <p class="text-sm font-medium">Saya adalah</p>
            <div class="mt-2 grid grid-cols-2 gap-3">
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 p-3 text-sm">
                    <input type="radio" wire:model.live="partner_type" value="seller"> Developer / Agen properti
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 p-3 text-sm">
                    <input type="radio" wire:model.live="partner_type" value="architect"> Arsitek
                </label>
            </div>
            @error('partner_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-2 text-sm font-semibold text-gray-700">Data akun</legend>
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
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-2 text-sm font-semibold text-gray-700">Data usaha</legend>
            <div>
                <label for="business_name" class="block text-sm font-medium">{{ $partner_type === 'architect' ? 'Nama biro / praktik' : 'Nama perusahaan / agen' }}</label>
                <input id="business_name" type="text" wire:model="business_name" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                @error('business_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="city" class="block text-sm font-medium">Kota</label>
                <input id="city" type="text" wire:model="city" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @if ($partner_type === 'architect')
                <div class="sm:col-span-2">
                    <label for="license_number" class="block text-sm font-medium">Nomor izin / keanggotaan (mis. IAI)</label>
                    <input id="license_number" type="text" wire:model="license_number" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    @error('license_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
            <div class="sm:col-span-2">
                <label for="address" class="block text-sm font-medium">Alamat</label>
                <input id="address" type="text" wire:model="address" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium">Tentang usaha Anda</label>
                <textarea id="description" rows="3" wire:model="description" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"></textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700" wire:loading.attr="disabled">
            <span wire:loading.remove>Daftar sebagai mitra</span><span wire:loading>Memproses...</span>
        </button>
    </form>
</div>
