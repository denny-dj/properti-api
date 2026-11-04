@php
    $statusLabels = ['new' => ['Baru', 'bg-green-100 text-green-800'], 'contacted' => ['Dihubungi', 'bg-blue-100 text-blue-800'],
        'surveying' => ['Survei', 'bg-indigo-100 text-indigo-800'], 'committed' => ['Sepakat', 'bg-emerald-100 text-emerald-800'],
        'lost' => ['Batal', 'bg-gray-200 text-gray-700']];
    $sourceLabels = ['property_detail' => 'Detail properti', 'architect_portfolio' => 'Portofolio arsitek', 'kpr_calculator' => 'Kalkulator KPR'];
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm';
@endphp
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold">Prospek</h1>
        <p class="text-sm text-gray-500">Semua minat yang masuk lewat tombol WhatsApp dan form KPR. Hubungi, catat hasilnya, lalu buat invoice saat pembeli sepakat.</p>
    </div>

    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari kode, nama, nomor HP" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach ($statusLabels as $value => [$label])<option value="{{ $value }}">{{ $label }}</option>@endforeach
        </select>
        <select wire:model.live="source" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua sumber</option>
            @foreach ($sourceLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
        </select>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($leads as $lead)
            @php
                [$label, $classes] = $statusLabels[$lead->status->value] ?? [$lead->status->value, 'bg-gray-100 text-gray-700'];
                $ref = $refs[\App\Services\ConciergeLeadService::referenceKey($lead) ?? ''] ?? null;
            @endphp
            <div wire:key="lead-{{ $lead->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-sm font-semibold">{{ $lead->lead_code }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $classes }}">{{ $label }}</span>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $sourceLabels[$lead->source->value] ?? $lead->source->value }}</span>
                        </div>
                        <p class="mt-1 font-medium">{{ $lead->customer_name ?: 'Tanpa nama' }}
                            @if ($lead->customer_phone)<span class="font-normal text-gray-500">· {{ $lead->customer_phone }}</span>@endif
                        </p>
                        <p class="text-sm text-gray-500">
                            @if ($ref)
                                @if ($ref['url'])<a href="{{ $ref['url'] }}" target="_blank" class="text-blue-700 hover:underline">{{ $ref['title'] }}</a>@else{{ $ref['title'] }} (sudah tidak tayang)@endif ·
                            @endif
                            @if ($lead->estimated_budget)Anggaran <x-rupiah :value="$lead->estimated_budget" /> ·@endif
                            @if ($lead->payment_preference){{ $lead->payment_preference->label() }} ·@endif
                            {{ $lead->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        @if ($lead->customer_phone)
                            <a href="https://wa.me/{{ $lead->customer_phone }}" target="_blank" rel="noopener" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Chat WhatsApp</a>
                        @endif
                        @if ($lead->status->value !== 'lost')
                            <a href="{{ route('admin.invoices', ['lead' => $lead->lead_code]) }}" class="rounded-lg border border-blue-300 px-3 py-1.5 font-medium text-blue-700 hover:bg-blue-50">Buat invoice</a>
                        @endif
                        <button type="button" wire:click="startEdit({{ $lead->id }}, '{{ $lead->status->value }}')" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Ubah status</button>
                    </div>
                </div>

                @if ($lead->notes)
                    <p class="whitespace-pre-line rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">{{ $lead->notes }}</p>
                @endif
                @if ($lead->handler)<p class="text-xs text-gray-400">Ditangani {{ $lead->handler->name }}</p>@endif

                @if ($editing === $lead->id)
                    <div class="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Status baru</label>
                                <select wire:model="newStatus" class="mt-1 {{ $input }}">
                                    <option value="contacted">Dihubungi</option>
                                    <option value="surveying">Survei</option>
                                    <option value="committed">Sepakat</option>
                                    <option value="lost">Batal</option>
                                </select>
                                @error('newStatus')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600">Catatan (opsional)</label>
                                <textarea rows="2" wire:model="newNote" class="mt-1 {{ $input }}" placeholder="mis. Survei Sabtu 10.00, minat paket custom"></textarea>
                                @error('newNote')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="saveStatus" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
                            <button type="button" wire:click="cancelEdit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-white">Batal</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada prospek pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $leads->links() }}</div>
</div>
