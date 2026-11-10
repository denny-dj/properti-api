<x-layouts.app title="Simulasi KPR">
<div class="min-h-screen bg-slate-50/50 pb-20">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb Editorial --}}
        <nav class="mb-4 flex items-center gap-2 text-xs font-medium text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-slate-700">Simulasi KPR</span>
        </nav>

        {{-- Header Halaman --}}
        <div class="mb-8 border-b border-slate-200/80 pb-6">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <x-icon name="wallet" class="h-3.5 w-3.5 text-blue-600" /> Fasilitas Pembiayaan Bank Rekanan
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-600" /> Transparan Tanpa Biaya Tersembunyi
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Simulasi KPR</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-2xl">
                Perkirakan cicilan bulanan berdasarkan harga, uang muka, bunga, dan tenor. Rencanakan skema pembiayaan hunian idaman Anda secara mandiri dan akurat.
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-12 items-start">
            
            {{-- Bagian Utama: Kartu Kalkulator Livewire (8 Kolom) --}}
            <div class="lg:col-span-8">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm">
                    <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Kalkulator Angsuran Bulanan</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Sesuaikan parameter harga dan suku bunga untuk melihat simulasi angsuran.</p>
                        </div>
                        <span class="hidden sm:inline-flex rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 font-mono">
                            Metode Anuitas
                        </span>
                    </div>

                    {{-- Komponen Livewire Asli (Semua Logika Backend Tetap Utuh) --}}
                    <livewire:kpr-calculator />
                </div>

                {{-- Catatan Edukasi Suku Bunga --}}
                <div class="mt-6 rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Mengenal Skema Bunga Fixed vs Floating
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2 text-xs text-slate-600 leading-relaxed">
                        <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                            <p class="font-bold text-slate-900 mb-1">Masa Bunga Tetap (Fixed)</p>
                            <p>Besaran cicilan bulanan bersifat pasti dan tidak berubah selama periode promosi (umumnya 1 sampai 5 tahun pertama tenor).</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                            <p class="font-bold text-slate-900 mb-1">Masa Bunga Mengambang (Floating)</p>
                            <p>Setelah periode fixed berakhir, suku bunga akan disesuaikan secara berkala mengikuti suku bunga acuan Bank Indonesia (BI Rate).</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Kanan: Dokumen Persyaratan & Bantuan Concierge (4 Kolom Sticky) --}}
            <aside class="space-y-6 lg:col-span-4 lg:sticky lg:top-24">
                
                {{-- Kartu Persyaratan Berkas --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Persyaratan Dokumen</p>
                    <h3 class="mt-1 text-sm font-bold text-slate-900">Berkas Pengajuan KPR</h3>
                    
                    <ul class="mt-4 space-y-3 text-xs text-slate-600">
                        <li class="flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Salinan KTP Pemohon & Pasangan, Kartu Keluarga, dan NPWP Pribadi.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Slip gaji asli 3 bulan terakhir dan Surat Keterangan Kerja (bagi karyawan).</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Mutasi rekening koran operasional 3-6 bulan terakhir.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Legalitas usaha (SIUP/NIB) dan laporan keuangan (bagi wiraswasta).</span>
                        </li>
                    </ul>
                </div>

                {{-- Banner Pendampingan Admin Concierge --}}
                <div class="rounded-3xl bg-slate-900 p-6 text-white shadow-xl">
                    <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                        Pendampingan 1-on-1
                    </span>
                    <h3 class="mt-3 text-base font-bold text-white">Butuh Bantuan Konsultasi KPR?</h3>
                    <p class="mt-1.5 text-xs text-slate-300 leading-relaxed">
                        Tim concierge kami siap membantu pra-analisis kesiapan berkas Anda ke berbagai bank rekanan untuk memperbesar peluang persetujuan.
                    </p>
                    <a href="{{ route('properties.index') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-slate-900 shadow hover:bg-slate-100 transition-colors">
                        <span>Pilih Properti Dulu</span> &rarr;
                    </a>
                </div>

            </aside>

        </div>
    </div>
</div>
</x-layouts.app>