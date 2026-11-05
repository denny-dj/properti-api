@php
    // [label, min, max] (rupiah)
    $propertyBudgets = [
        ['Semua budget', null, null],
        ['Di bawah Rp 500 Jt', null, 500000000],
        ['Rp 500 Jt – 1 M', 500000000, 1000000000],
        ['Rp 1 – 2 M', 1000000000, 2000000000],
        ['Di atas Rp 2 M', 2000000000, null],
    ];
    $rabRanges = \App\Livewire\Pages\PortfolioIndex::RAB_RANGES;
    $field = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20';
@endphp
<section class="relative isolate overflow-hidden bg-slate-900 text-white">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(60rem_30rem_at_85%_-10%,rgba(37,99,235,0.35),transparent),radial-gradient(40rem_24rem_at_0%_110%,rgba(16,185,129,0.18),transparent)]"></div>
    <div class="mx-auto max-w-7xl px-4 py-14 md:py-24">
        <div class="max-w-3xl">
            <div class="flex flex-wrap gap-2 text-xs font-medium">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-emerald-300 ring-1 ring-white/15"><x-icon name="shield" class="h-4 w-4" /> Verified Partner</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-slate-200 ring-1 ring-white/15"><x-icon name="wallet" class="h-4 w-4" /> Pembayaran lewat platform</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-slate-200 ring-1 ring-white/15"><x-icon name="chat" class="h-4 w-4" /> Didampingi admin via WhatsApp</span>
            </div>
            <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight md:text-5xl">
                Beli Rumah &amp; Desain Arsitek Lewat <span class="text-emerald-400">Concierge Resmi</span> Platform
            </h1>
            <p class="mt-4 max-w-2xl text-base text-slate-300 md:text-lg">
                Properti terverifikasi dan desain arsitek dengan estimasi RAB yang jelas. Tim admin kami menyaring pilihan, mendampingi survei, dan mengamankan pembayaran Anda.
            </p>
        </div>

        {{-- Pencarian ringkas: tab Properti / Arsitek --}}
        <form method="GET" action="{{ route('properties.index') }}"
              x-data="{ type: 'properti', pb: 0, ranges: @js(array_map(fn ($r) => [$r[1], $r[2]], $propertyBudgets)) }"
              x-bind:action="type === 'properti' ? '{{ route('properties.index') }}' : '{{ route('portfolios.index') }}'"
              class="mt-8 max-w-4xl rounded-2xl bg-white/95 p-3 text-slate-900 shadow-2xl shadow-slate-950/30 backdrop-blur">
            <div class="mb-3 inline-flex rounded-xl bg-slate-100 p-1 text-sm font-semibold" role="tablist">
                <button type="button" role="tab" x-on:click="type = 'properti'"
                        x-bind:class="type === 'properti' ? 'bg-white text-slate-900 shadow' : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg bg-white px-4 py-2 text-slate-900 shadow transition">Properti</button>
                <button type="button" role="tab" x-on:click="type = 'arsitek'"
                        x-bind:class="type === 'arsitek' ? 'bg-white text-slate-900 shadow' : 'text-slate-500 hover:text-slate-700'"
                        class="rounded-lg px-4 py-2 text-slate-500 transition">Arsitek</button>
            </div>

            <div class="grid gap-3 md:grid-cols-[1.4fr_1fr_1fr_auto]">
                <input type="text" name="q" class="{{ $field }}"
                       x-bind:placeholder="type === 'properti' ? 'Cari nama properti atau area' : 'Cari desain, mis. rumah 2 lantai'"
                       placeholder="Cari nama properti atau area" aria-label="Kata kunci">

                <div x-show="type === 'properti'">
                    <input type="text" name="city" class="{{ $field }}" placeholder="Lokasi / kota" aria-label="Lokasi"
                           x-bind:disabled="type !== 'properti'">
                </div>
                <div x-show="type === 'arsitek'" style="display: none">
                    <select name="rab" class="{{ $field }}" aria-label="Estimasi RAB" x-bind:disabled="type !== 'arsitek'">
                        <option value="">Semua estimasi RAB</option>
                        @foreach ($rabRanges as $key => $range)
                            <option value="{{ $key }}">{{ $range[2] }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="type === 'properti'">
                    <select x-model.number="pb" class="{{ $field }}" aria-label="Budget">
                        @foreach ($propertyBudgets as $i => $budget)
                            <option value="{{ $i }}">{{ $budget[0] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="min_price" x-bind:value="ranges[pb][0]" x-bind:disabled="type !== 'properti' || ranges[pb][0] === null">
                    <input type="hidden" name="max_price" x-bind:value="ranges[pb][1]" x-bind:disabled="type !== 'properti' || ranges[pb][1] === null">
                </div>
                <div x-show="type === 'arsitek'" style="display: none" class="hidden md:block">
                    <select name="ready" class="{{ $field }}" aria-label="Jenis paket" x-bind:disabled="type !== 'arsitek'">
                        <option value="">Semua paket</option>
                        <option value="1">Desain siap bangun</option>
                    </select>
                </div>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <x-icon name="search" class="h-4 w-4" /> Cari
                </button>
            </div>
        </form>
    </div>
</section>
