<x-layouts.app title="Cara Kerja">
<div class="min-h-screen bg-slate-50/50 pb-20">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb Editorial --}}
        <nav class="mb-4 flex items-center gap-2 text-xs font-medium text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-slate-700">Cara Kerja & Escrow</span>
        </nav>

        {{-- Header Halaman --}}
        <div class="mb-10 border-b border-slate-200/80 pb-6">
            <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 mb-3">
                <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-600" /> Transaksi Terproteksi Rekening Penampung Resmi
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">{{ $content['title'] }}</h1>
            <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">{{ $content['subtitle'] }}</p>
        </div>

        {{-- Timeline Tahapan Kerja --}}
        <ol class="relative space-y-8 before:absolute before:left-5 before:top-4 before:bottom-4 before:w-0.5 before:bg-slate-200">
            @foreach ($content['steps'] as $step)
                <li class="relative flex items-start gap-4 sm:gap-6">
                    {{-- Badge Nomor Tahap --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-900 font-mono text-sm font-bold text-white shadow-md ring-4 ring-white z-10">
                        {{ $step['step'] }}
                    </div>

                    {{-- Isi Kartu Tahapan --}}
                    <div class="flex-1 rounded-3xl border border-slate-200/80 bg-white p-5 sm:p-7 shadow-sm transition-all hover:shadow-md">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Tahap {{ $step['step'] }}</span>
                        <h2 class="mt-1 text-lg sm:text-xl font-bold text-slate-900">{{ $step['title'] }}</h2>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $step['description'] }}</p>

                        {{-- Pilihan Opsi Jalur (Jika Tersedia di Tahapan Ini) --}}
                        @if (! empty($step['options']))
                            <div class="mt-5 grid gap-3 sm:grid-cols-2 md:grid-cols-3 pt-4 border-t border-slate-100">
                                @foreach ($step['options'] as $option)
                                    <div class="rounded-2xl border border-slate-200/90 bg-slate-50/60 p-4 transition-all hover:bg-white hover:border-slate-300 hover:shadow-sm">
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-blue-700 uppercase">
                                            Pilihan {{ $option['code'] }}
                                        </span>
                                        <p class="mt-2 text-xs font-bold text-slate-900">{{ $option['title'] }}</p>
                                        <p class="mt-1 text-[11px] text-slate-500 leading-relaxed">{{ $option['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        {{-- Escrow Trust Notice --}}
        <div class="mt-12 overflow-hidden rounded-3xl bg-slate-900 p-6 sm:p-8 text-white shadow-xl">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <x-icon name="shield" class="h-6 w-6" />
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Jaminan Transaksi Platform</p>
                    <h3 class="text-base sm:text-lg font-bold text-white">Ketentuan Pembayaran & Proteksi Escrow</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-300 leading-relaxed">
                        {{ $content['payment_note'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Action CTA --}}
        <div class="mt-10 flex flex-wrap items-center gap-3 pt-6 border-t border-slate-200/80">
            <a href="{{ route('portfolios.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                <span>Eksplorasi Portofolio</span> &rarr;
            </a>
            <a href="{{ route('portfolios.index', ['ready' => 1]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-400 transition-all">
                <x-icon name="blueprint" class="h-4 w-4 text-emerald-600" />
                <span>Desain Siap Bangun</span>
            </a>
            <a href="{{ route('kpr') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-400 transition-all">
                <span>Simulasi Angsuran KPR</span>
            </a>
        </div>

    </div>
</div>
</x-layouts.app>