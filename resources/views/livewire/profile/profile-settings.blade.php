<div class="mx-auto max-w-4xl px-4 sm:px-6">
    @php
        $tabs = [['profil', 'Profil Saya']];
        if ($profile) {
            $tabs[] = ['bisnis', $isArchitect ? 'Branding Studio' : 'Profil Bisnis'];
        }
        $tabs[] = ['keamanan', 'Keamanan'];

        $labelClass = 'block text-[11px] font-semibold uppercase tracking-wider text-slate-500';
        $inputClass = 'mt-2 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 transition focus:border-slate-900 focus:bg-white focus:outline-none focus:ring-0';
        $cardClass = 'rounded-3xl border border-slate-200/80 bg-white';
        $primaryClass = 'inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-60';
        $ghostClass = 'inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';

        $roleLabel = $user->hasRole('admin') ? 'Administrator' : ($profile ? ($isArchitect ? 'Arsitek' : 'Seller / Developer') : 'Pembeli');
        $verification = $profile ? match ($profile->verification_status->value) {
            'approved' => ['Terverifikasi', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
            'rejected' => ['Verifikasi ditolak', 'bg-red-50 text-red-700 ring-red-600/20'],
            default => ['Menunggu verifikasi', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        } : null;
        $emailChanged = mb_strtolower(trim($email)) !== mb_strtolower((string) $user->email);
    @endphp

    {{-- Kepala halaman --}}
    <div class="mb-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Pengaturan akun</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Profil &amp; Personalisasi</h1>
    </div>

    <div class="{{ $cardClass }} p-6">
        <div class="flex flex-wrap items-center gap-5">
            <img src="{{ $user->avatar_url }}" alt="Foto profil {{ $user->name }}" class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xl font-bold text-slate-900">{{ $user->name }}</p>
                <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $roleLabel }}</span>
                    @if ($user->hasVerifiedEmail())
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Email terverifikasi</span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Email belum diverifikasi</span>
                    @endif
                    @if ($verification)
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $verification[1] }}">{{ $verification[0] }}</span>
                    @endif
                </div>
            </div>
        </div>
        @if ($profile && $profile->verification_status->value === 'rejected' && $profile->rejection_reason)
            <p class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">Alasan penolakan: {{ $profile->rejection_reason }}</p>
        @endif
    </div>

    {{-- Tab --}}
    <div class="mt-6 overflow-x-auto border-b border-slate-200" role="tablist" aria-label="Pengaturan profil">
        <div class="flex min-w-max gap-8">
            @foreach ($tabs as [$key, $label])
                <button type="button" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')" wire:key="tab-{{ $key }}"
                        class="-mb-px border-b-2 px-1 pb-3 text-sm font-semibold transition {{ $tab === $key ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===================== TAB 1: PROFIL SAYA ===================== --}}
    @if ($tab === 'profil')
        <form wire:submit="saveProfile" class="{{ $cardClass }} mt-6 p-6 sm:p-8" wire:key="panel-profil">
            <div class="grid gap-8 md:grid-cols-[13rem_1fr]">
                <div>
                    <span class="{{ $labelClass }}">Foto profil</span>
                    <img src="{{ $avatar && $avatar->isPreviewable() ? $avatar->temporaryUrl() : $user->avatar_url }}" alt="" class="mt-3 h-36 w-36 rounded-3xl object-cover ring-1 ring-slate-200">

                    <div class="mt-4 flex flex-wrap gap-2">
                        <label for="avatar-input" class="{{ $ghostClass }}">Pilih foto</label>
                        <input id="avatar-input" type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        @if ($user->hasCustomAvatar())
                            <button type="button" wire:click="removeAvatar" wire:confirm="Hapus foto profil?" class="inline-flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                        @endif
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-400">JPG, PNG, atau WEBP. Maksimal 2 MB. Foto dipotong persegi dan data lokasi di dalamnya dihapus.</p>
                    <p wire:loading wire:target="avatar" class="mt-2 text-xs font-medium text-slate-500">Mengunggah...</p>
                    @error('avatar')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="name" class="{{ $labelClass }}">Nama lengkap</label>
                        <input id="name" type="text" wire:model="name" autocomplete="name" class="{{ $inputClass }}">
                        @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClass }}">Email</label>
                        <input id="email" type="email" wire:model.live.debounce.500ms="email" autocomplete="email" class="{{ $inputClass }}">
                        @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    @if ($emailChanged)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4">
                            <p class="text-sm text-amber-800">Mengganti email membutuhkan konfirmasi kata sandi, lalu alamat baru harus diverifikasi ulang.</p>
                            <label for="email-password" class="{{ $labelClass }} mt-3">Kata sandi Anda</label>
                            <input id="email-password" type="password" wire:model="emailPassword" autocomplete="current-password" class="{{ $inputClass }} bg-white">
                            @error('emailPassword')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div>
                        <label for="phone" class="{{ $labelClass }}">Nomor HP / WhatsApp</label>
                        <input id="phone" type="tel" inputmode="tel" wire:model="phone" autocomplete="tel" placeholder="081234567890" class="{{ $inputClass }}">
                        @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile,avatar" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="saveProfile">Simpan perubahan</span>
                    <span wire:loading wire:target="saveProfile">Menyimpan...</span>
                </button>
                @if ($accountNotice)<p class="text-sm font-medium text-emerald-700">{{ $accountNotice }}</p>@endif
            </div>
        </form>
    @endif

    {{-- ===================== TAB 2: BRANDING STUDIO / BISNIS ===================== --}}
    @if ($tab === 'bisnis' && $profile)
        <form wire:submit="saveBusiness" class="{{ $cardClass }} mt-6 p-6 sm:p-8" wire:key="panel-bisnis">
            <div class="grid gap-8 md:grid-cols-[13rem_1fr]">
                <div>
                    <span class="{{ $labelClass }}">{{ $isArchitect ? 'Logo studio' : 'Logo perusahaan' }}</span>
                    <img src="{{ $logo && $logo->isPreviewable() ? $logo->temporaryUrl() : $profile->logo_url }}" alt="" class="mt-3 h-36 w-36 rounded-3xl bg-slate-50 object-contain p-2 ring-1 ring-slate-200">

                    <div class="mt-4 flex flex-wrap gap-2">
                        <label for="logo-input" class="{{ $ghostClass }}">Pilih logo</label>
                        <input id="logo-input" type="file" wire:model="logo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        @if ($profile->hasCustomLogo())
                            <button type="button" wire:click="removeLogo" wire:confirm="Hapus logo?" class="inline-flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                        @endif
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-400">JPG, PNG, atau WEBP. Maksimal 2 MB. Latar PNG transparan dipertahankan.</p>
                    <p wire:loading wire:target="logo" class="mt-2 text-xs font-medium text-slate-500">Mengunggah...</p>
                    @error('logo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="business_name" class="{{ $labelClass }}">{{ $isArchitect ? 'Nama studio' : 'Nama perusahaan / developer' }}</label>
                        <input id="business_name" type="text" wire:model="business_name" class="{{ $inputClass }}">
                        @error('business_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="business_phone" class="{{ $labelClass }}">Telepon bisnis</label>
                            <input id="business_phone" type="tel" wire:model="business_phone" placeholder="021 5551234" class="{{ $inputClass }}">
                            @error('business_phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="city" class="{{ $labelClass }}">Kota</label>
                            <input id="city" type="text" wire:model="city" class="{{ $inputClass }}">
                            @error('city')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="address" class="{{ $labelClass }}">Alamat</label>
                        <textarea id="address" rows="2" wire:model="address" class="{{ $inputClass }}"></textarea>
                        @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="{{ $labelClass }}">{{ $isArchitect ? 'Filosofi studio' : 'Profil developer' }}</label>
                        <textarea id="description" rows="5" wire:model="description" class="{{ $inputClass }}"
                                  placeholder="{{ $isArchitect ? 'Pendekatan desain, nilai, dan keunggulan studio Anda.' : 'Pengalaman, proyek unggulan, dan keunggulan perusahaan Anda.' }}"></textarea>
                        @error('description')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="license_number" class="{{ $labelClass }}">{{ $isArchitect ? 'No. registrasi / IAI' : 'No. izin usaha / lisensi' }}</label>
                            <input id="license_number" type="text" wire:model="license_number" class="{{ $inputClass }}">
                            @error('license_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="portfolio_url" class="{{ $labelClass }}">Tautan portofolio</label>
                            <input id="portfolio_url" type="url" wire:model="portfolio_url" placeholder="https://" class="{{ $inputClass }}">
                            @error('portfolio_url')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @if (config('concierge.partner_reverify_on_identity_change', false) && $profile->isApproved())
                        <p class="rounded-2xl bg-amber-50 px-4 py-3 text-xs text-amber-800">Mengubah nama usaha atau nomor registrasi akan memicu peninjauan ulang oleh admin sebelum akun mitra Anda dapat dipakai kembali.</p>
                    @endif
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveBusiness,logo" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="saveBusiness">Simpan profil bisnis</span>
                    <span wire:loading wire:target="saveBusiness">Menyimpan...</span>
                </button>
                @if ($businessNotice)<p class="text-sm font-medium text-emerald-700">{{ $businessNotice }}</p>@endif
            </div>
        </form>
    @endif

    {{-- ===================== TAB 3: KEAMANAN ===================== --}}
    @if ($tab === 'keamanan')
        <form wire:submit="updatePassword" class="{{ $cardClass }} mt-6 p-6 sm:p-8" wire:key="panel-keamanan">
            <div class="max-w-md space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Ganti kata sandi</h2>
                    <p class="mt-1 text-sm text-slate-500">Gunakan kombinasi minimal 8 karakter yang tidak dipakai di layanan lain.</p>
                </div>

                <div>
                    <label for="current_password" class="{{ $labelClass }}">Kata sandi saat ini</label>
                    <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" class="{{ $inputClass }}">
                    @error('current_password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="{{ $labelClass }}">Kata sandi baru</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" class="{{ $inputClass }}">
                    @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $labelClass }}">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="{{ $inputClass }}">
                </div>

                <p class="rounded-2xl bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500">Setelah kata sandi diganti, token API yang pernah dibuat untuk akun ini dicabut. Aplikasi yang memakainya perlu masuk kembali.</p>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="{{ $primaryClass }}">
                    <span wire:loading.remove wire:target="updatePassword">Perbarui kata sandi</span>
                    <span wire:loading wire:target="updatePassword">Memproses...</span>
                </button>
                @if ($passwordNotice)<p class="text-sm font-medium text-emerald-700">{{ $passwordNotice }}</p>@endif
            </div>
        </form>
    @endif
</div>
