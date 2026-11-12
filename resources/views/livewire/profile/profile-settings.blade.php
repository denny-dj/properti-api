{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="mx-auto max-w-5xl px-4 sm:px-6 space-y-6 pb-12">
    @php
        $tabs = [['profil', 'Profil Saya']];
        if ($profile) {
            $tabs[] = ['bisnis', $isArchitect ? 'Branding Studio' : 'Profil Bisnis'];
        }
        $tabs[] = ['keamanan', 'Keamanan'];

        $labelClass = 'block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5';
        $inputClass = 'w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all';
        $cardClass = 'rounded-3xl border border-slate-200/80 bg-white shadow-sm';
        $primaryClass = 'inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50';
        $ghostClass = 'inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all';

        $roleLabel = $user->hasRole('admin') ? 'Administrator' : ($profile ? ($isArchitect ? 'Arsitek' : 'Seller / Developer') : 'Pembeli');
        $verification = $profile ? match ($profile->verification_status->value) {
            'approved' => ['Terverifikasi', 'border-emerald-200/80 bg-emerald-50 text-emerald-800', 'bg-emerald-500'],
            'rejected' => ['Verifikasi ditolak', 'border-rose-200/80 bg-rose-50 text-rose-800', 'bg-rose-500'],
            default => ['Menunggu verifikasi', 'border-amber-200/80 bg-amber-50 text-amber-800', 'bg-amber-500'],
        } : null;
        $emailChanged = mb_strtolower(trim($email)) !== mb_strtolower((string) $user->email);

        $dashboardUrl = $user->hasRole('admin')
            ? route('admin.dashboard')
            : ($user->hasRole('partner') ? route('partner.dashboard') : url('/'));
        $dashboardLabel = $user->hasRole('admin')
            ? 'Panel Admin'
            : ($user->hasRole('partner') ? 'Portal Mitra' : 'Beranda');
    @endphp

    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ $dashboardUrl }}" class="hover:text-blue-600 transition-colors">{{ $dashboardLabel }}</a>
                <span>/</span>
                <span class="text-slate-700">Pengaturan Akun</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Profil &amp; Personalisasi</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Kelola identitas akun, informasi branding bisnis, serta pengaturan keamanan kredensial Anda.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="font-mono rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-500 shadow-2xs">
                ID #{{ $user->id }}
            </span>
        </div>
    </div>

    {{-- Kartu Ringkasan Identitas Pengguna --}}
    <div class="{{ $cardClass }} p-6 sm:p-7">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="relative shrink-0">
                <img src="{{ $user->avatar_url }}" 
                     alt="Foto profil {{ $user->name }}" 
                     class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200 shadow-2xs">
                <span class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-white ring-1 ring-slate-200">
                    <span class="h-2.5 w-2.5 rounded-full {{ $user->isActive() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                </span>
            </div>

            <div class="min-w-0 flex-1 space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h2>
                    <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                        {{ $roleLabel }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                    <span>{{ $user->email }}</span>
                    @if ($user->phone)
                        <span class="text-slate-300">·</span>
                        <span class="font-mono text-slate-600">{{ $user->phone }}</span>
                    @endif
                </div>

                <div class="pt-1.5 flex flex-wrap items-center gap-2">
                    @if ($user->hasVerifiedEmail())
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200/80 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span>Email terverifikasi</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200/80 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            <span>Email belum diverifikasi</span>
                        </span>
                    @endif

                    @if ($verification)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $verification[1] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $verification[2] }}"></span>
                            <span>{{ $verification[0] }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if ($profile && $profile->verification_status->value === 'rejected' && $profile->rejection_reason)
            <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50/70 p-4 text-xs text-rose-800 flex items-start gap-2.5">
                <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <span class="font-bold uppercase tracking-wider text-rose-950 block mb-0.5">Alasan Penolakan Verifikasi</span>
                    <p class="leading-relaxed">{{ $profile->rejection_reason }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Navigasi Tab Editorial --}}
    <div class="overflow-x-auto">
        <div class="inline-flex min-w-full sm:min-w-0 rounded-2xl border border-slate-200/80 bg-white p-1.5 shadow-2xs gap-1" role="tablist" aria-label="Pengaturan profil">
            @foreach ($tabs as [$key, $label])
                <button type="button" 
                        role="tab" 
                        aria-selected="{{ $tab === $key ? 'true' : 'false' }}" 
                        wire:click="setTab('{{ $key }}')" 
                        wire:key="tab-{{ $key }}"
                        class="inline-flex flex-1 sm:flex-initial items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 py-2 text-xs font-bold transition-all cursor-pointer {{ $tab === $key ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    @if ($key === 'profil')
                        <svg class="h-3.5 w-3.5 {{ $tab === $key ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @elseif ($key === 'bisnis')
                        <svg class="h-3.5 w-3.5 {{ $tab === $key ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    @else
                        <svg class="h-3.5 w-3.5 {{ $tab === $key ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    @endif
                    <span>{{ $label }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===================== TAB 1: PROFIL SAYA ===================== --}}
    @if ($tab === 'profil')
        <form wire:submit="saveProfile" class="{{ $cardClass }} p-6 sm:p-7 space-y-6" wire:key="panel-profil">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-sm font-bold text-slate-900">Informasi Akun &amp; Kontak Personal</h2>
                <p class="mt-0.5 text-xs text-slate-400">Perbarui foto profil, nama tampilan, alamat email, dan nomor WhatsApp aktif Anda.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-[13rem_1fr]">
                {{-- Kolom Avatar --}}
                <div class="space-y-3">
                    <span class="{{ $labelClass }}">Foto profil</span>
                    <div class="relative w-36">
                        <img src="{{ $avatar && $avatar->isPreviewable() ? $avatar->temporaryUrl() : $user->avatar_url }}" 
                             alt="" 
                             class="h-36 w-36 rounded-3xl object-cover ring-1 ring-slate-200 shadow-2xs">
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <label for="avatar-input" class="{{ $ghostClass }}">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Pilih foto</span>
                        </label>
                        <input id="avatar-input" type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        
                        @if ($user->hasCustomAvatar())
                            <button type="button" 
                                    wire:click="removeAvatar" 
                                    wire:confirm="Hapus foto profil?" 
                                    class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                <span>Hapus</span>
                            </button>
                        @endif
                    </div>

                    <p class="text-[11px] leading-relaxed text-slate-400">JPG, PNG, atau WEBP. Maksimal 2 MB. Foto dipotong persegi dan data lokasi di dalamnya dihapus.</p>
                    <p wire:loading wire:target="avatar" class="text-xs font-semibold text-slate-600">Mengunggah pratinjau...</p>
                    @error('avatar')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                {{-- Kolom Input Identitas --}}
                <div class="space-y-5">
                    <div>
                        <label for="name" class="{{ $labelClass }}">Nama lengkap</label>
                        <input id="name" type="text" wire:model="name" autocomplete="name" class="{{ $inputClass }}">
                        @error('name')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClass }}">Email</label>
                        <input id="email" type="email" wire:model.live.debounce.500ms="email" autocomplete="email" class="{{ $inputClass }}">
                        @error('email')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    @if ($emailChanged)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 space-y-3">
                            <div class="flex items-start gap-2 text-xs text-amber-900">
                                <svg class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="leading-relaxed font-medium">Mengganti email membutuhkan konfirmasi kata sandi, lalu alamat baru harus diverifikasi ulang.</span>
                            </div>
                            <div>
                                <label for="email-password" class="{{ $labelClass }}">Kata sandi Anda</label>
                                <input id="email-password" type="password" wire:model="emailPassword" autocomplete="current-password" class="{{ $inputClass }} bg-white">
                                @error('emailPassword')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="phone" class="{{ $labelClass }}">Nomor HP / WhatsApp</label>
                        <input id="phone" type="tel" inputmode="tel" wire:model="phone" autocomplete="tel" placeholder="081234567890" class="{{ $inputClass }} font-mono">
                        @error('phone')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Footer Aksi Tab 1 --}}
            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile,avatar" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="saveProfile">Simpan perubahan</span>
                    <span wire:loading wire:target="saveProfile" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>

                @if ($accountNotice)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $accountNotice }}</span>
                    </div>
                @endif
            </div>
        </form>
    @endif

    {{-- ===================== TAB 2: BRANDING STUDIO / BISNIS ===================== --}}
    @if ($tab === 'bisnis' && $profile)
        <form wire:submit="saveBusiness" class="{{ $cardClass }} p-6 sm:p-7 space-y-6" wire:key="panel-bisnis">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-sm font-bold text-slate-900">
                    {{ $isArchitect ? 'Identitas Studio & Kredensial Arsitek' : 'Identitas Perusahaan & Legalitas Developer' }}
                </h2>
                <p class="mt-0.5 text-xs text-slate-400">Informasi ini ditampilkan pada katalog mitra dan menjadi rujukan kredibilitas bagi calon klien.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-[13rem_1fr]">
                {{-- Kolom Logo --}}
                <div class="space-y-3">
                    <span class="{{ $labelClass }}">{{ $isArchitect ? 'Logo studio' : 'Logo perusahaan' }}</span>
                    <div class="relative w-36">
                        <img src="{{ $logo && $logo->isPreviewable() ? $logo->temporaryUrl() : $profile->logo_url }}" 
                             alt="" 
                             class="h-36 w-36 rounded-3xl bg-slate-50 object-contain p-2.5 ring-1 ring-slate-200 shadow-2xs">
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <label for="logo-input" class="{{ $ghostClass }}">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Pilih logo</span>
                        </label>
                        <input id="logo-input" type="file" wire:model="logo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        
                        @if ($profile->hasCustomLogo())
                            <button type="button" 
                                    wire:click="removeLogo" 
                                    wire:confirm="Hapus logo?" 
                                    class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                <span>Hapus</span>
                            </button>
                        @endif
                    </div>

                    <p class="text-[11px] leading-relaxed text-slate-400">JPG, PNG, atau WEBP. Maksimal 2 MB. Latar PNG transparan dipertahankan.</p>
                    <p wire:loading wire:target="logo" class="text-xs font-semibold text-slate-600">Mengunggah logo...</p>
                    @error('logo')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                {{-- Kolom Input Profil Bisnis --}}
                <div class="space-y-5">
                    <div>
                        <label for="business_name" class="{{ $labelClass }}">{{ $isArchitect ? 'Nama studio' : 'Nama perusahaan / developer' }}</label>
                        <input id="business_name" type="text" wire:model="business_name" class="{{ $inputClass }}">
                        @error('business_name')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="business_phone" class="{{ $labelClass }}">Telepon bisnis</label>
                            <input id="business_phone" type="tel" wire:model="business_phone" placeholder="021 5551234" class="{{ $inputClass }} font-mono">
                            @error('business_phone')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="city" class="{{ $labelClass }}">Kota</label>
                            <input id="city" type="text" wire:model="city" class="{{ $inputClass }}">
                            @error('city')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="address" class="{{ $labelClass }}">Alamat</label>
                        <textarea id="address" rows="2" wire:model="address" class="{{ $inputClass }}"></textarea>
                        @error('address')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="{{ $labelClass }}">{{ $isArchitect ? 'Filosofi studio' : 'Profil developer' }}</label>
                        <textarea id="description" rows="4" wire:model="description" class="{{ $inputClass }}"
                                  placeholder="{{ $isArchitect ? 'Pendekatan desain, nilai, dan keunggulan studio Anda.' : 'Pengalaman, proyek unggulan, dan keunggulan perusahaan Anda.' }}"></textarea>
                        @error('description')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="license_number" class="{{ $labelClass }}">{{ $isArchitect ? 'No. registrasi / IAI' : 'No. izin usaha / lisensi' }}</label>
                            <input id="license_number" type="text" wire:model="license_number" class="{{ $inputClass }} font-mono">
                            @error('license_number')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="portfolio_url" class="{{ $labelClass }}">Tautan portofolio</label>
                            <input id="portfolio_url" type="url" wire:model="portfolio_url" placeholder="https://" class="{{ $inputClass }} font-mono">
                            @error('portfolio_url')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @if (config('concierge.partner_reverify_on_identity_change', false) && $profile->isApproved())
                        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/70 p-3.5 text-xs text-amber-800 flex items-start gap-2">
                            <svg class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Mengubah nama usaha atau nomor registrasi akan memicu peninjauan ulang oleh admin sebelum akun mitra Anda dapat dipakai kembali.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer Aksi Tab 2 --}}
            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveBusiness,logo" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="saveBusiness">Simpan profil bisnis</span>
                    <span wire:loading wire:target="saveBusiness" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>

                @if ($businessNotice)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $businessNotice }}</span>
                    </div>
                @endif
            </div>
        </form>
    @endif

    {{-- ===================== TAB 3: KEAMANAN ===================== --}}
    @if ($tab === 'keamanan')
        <form wire:submit="updatePassword" class="{{ $cardClass }} p-6 sm:p-7 space-y-6" wire:key="panel-keamanan">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-sm font-bold text-slate-900">Ganti kata sandi</h2>
                <p class="mt-0.5 text-xs text-slate-400">Gunakan kombinasi minimal 8 karakter yang tidak dipakai di layanan lain.</p>
            </div>

            <div class="max-w-lg space-y-5">
                <div>
                    <label for="current_password" class="{{ $labelClass }}">Kata sandi saat ini</label>
                    <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" class="{{ $inputClass }}">
                    @error('current_password')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="{{ $labelClass }}">Kata sandi baru</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" class="{{ $inputClass }}">
                    @error('password')<p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $labelClass }}">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="{{ $inputClass }}">
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 text-xs leading-relaxed text-slate-600 flex items-start gap-2.5">
                    <svg class="h-4 w-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Setelah kata sandi diganti, token API yang pernah dibuat untuk akun ini dicabut. Aplikasi yang memakainya perlu masuk kembali.</span>
                </div>
            </div>

            {{-- Footer Aksi Tab 3 --}}
            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5">
                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="updatePassword">Perbarui kata sandi</span>
                    <span wire:loading wire:target="updatePassword" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Memproses...</span>
                    </span>
                </button>

                @if ($passwordNotice)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $passwordNotice }}</span>
                    </div>
                @endif
            </div>
        </form>
    @endif
</div>