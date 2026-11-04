@php
    $importDraft = $importDraft ?? session('purchase_scan_import');

    $oldItems = old('items');

    $transactionItems = isset($purchaseTransaction)
        ? $purchaseTransaction->items->map(function ($item) {
            return [
                'material_code' => $item->material?->code ?? '',
                'material_id' => $item->material_id,
                'material_name_snapshot' => $item->material_name_snapshot,
                'unit_name_snapshot' => $item->unit_name_snapshot,
                'qty' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
                'is_imported_scan' => false,
            ];
        })->toArray()
        : [];

    $importItems = collect($importDraft['items'] ?? [])->map(function ($item) {
        $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 1);
        if ($qty <= 0) $qty = 1;
        $price    = (float) ($item['price'] ?? $item['harga'] ?? 0);
        $subtotal = (float) ($item['subtotal'] ?? $item['total'] ?? 0);
        if ($subtotal <= 0) $subtotal = $qty * $price;
        return [
            'material_code'           => $item['material_code'] ?? $item['kode'] ?? '',
            'material_id'             => $item['material_id'] ?? '',
            'material_name_snapshot'  => $item['material_name_snapshot'] ?? $item['material_name'] ?? $item['nama_material'] ?? '',
            'unit_name_snapshot'      => $item['unit_name_snapshot'] ?? $item['unit_name'] ?? $item['satuan'] ?? '',
            'qty'                     => $qty,
            'price'                   => $price,
            'subtotal'                => $subtotal,
            'notes'                   => $item['notes'] ?? '',
            'is_imported_scan'        => true,
        ];
    })->values()->toArray();

    $items = $oldItems ?: ($transactionItems ?: ($importItems ?: [[
        'material_code' => '', 'material_id' => '',
        'material_name_snapshot' => '', 'unit_name_snapshot' => '',
        'qty' => 1, 'price' => 0, 'subtotal' => 0,
        'notes' => '', 'is_imported_scan' => false,
    ]]));

    $materialOptions = $materials->map(fn($m) => [
        'id'    => $m->id,
        'code'  => $m->code,
        'name'  => $m->name,
        'unit'  => $m->unit ? $m->unit->name : '',
        'price' => (float) $m->default_price,
    ])->values()->toArray();

    $importedItemCount = collect($items)->where('is_imported_scan', true)->count();
    $initialGrandTotal = collect($items)->sum(function ($item) {
        $sub = (float) ($item['subtotal'] ?? 0);
        return $sub > 0 ? $sub : (float)($item['qty'] ?? 0) * (float)($item['price'] ?? 0);
    });

    $initialTransactionDate = old('transaction_date',
        isset($purchaseTransaction)
            ? $purchaseTransaction->transaction_date?->format('Y-m-d')
            : ($importDraft['transaction_date'] ?? now()->format('Y-m-d'))
    );

    $selectedSupplierId  = old('supplier_id',  $purchaseTransaction->supplier_id  ?? ($importDraft['supplier_id']  ?? ''));
    $selectedProjectId   = old('project_id',   $purchaseTransaction->project_id   ?? ($importDraft['project_id']   ?? ''));
    $noteNumberValue     = old('note_number',  $purchaseTransaction->note_number  ?? ($importDraft['note_number']  ?? ''));
    $notesValue          = old('notes',        $purchaseTransaction->notes        ?? ($importDraft['notes']        ?? ''));
    $statusValue         = old('status',       $purchaseTransaction->status       ?? 'draft');
    $scannedSupplierName = $importDraft['supplier_name'] ?? null;
    $hasMatchedSupplier  = !empty($selectedSupplierId);
    $showMissingSupplierCard = !empty($scannedSupplierName) && empty($selectedSupplierId);
@endphp

@csrf
<input type="hidden" name="auto_create_missing_materials" id="auto-create-missing-materials" value="0">
<input type="hidden" name="input_source"       value="{{ $importDraft['input_source']       ?? 'manual' }}">
<input type="hidden" name="scan_source_type"   value="{{ $importDraft['scan_source_type']   ?? '' }}">
<input type="hidden" name="scan_reference_id"  value="{{ $importDraft['scan_reference_id']  ?? '' }}">

<div class="space-y-5">

    {{-- ── VALIDATION ERRORS ─────────────────────────────── --}}
    @if($errors->any())
    <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3.5">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100">
            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 004.91 19h14.18a2 2 0 001.72-2.84l-7.09-12.3a2 2 0 00-3.43 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-rose-800">Ada {{ $errors->count() }} kesalahan — periksa kembali</p>
            <ul class="mt-1.5 space-y-0.5 pl-4 text-sm text-rose-700 list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── SCAN IMPORT BANNER ─────────────────────────────── --}}
    @if($importDraft)
    <div class="flex items-center gap-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-emerald-800">Draft scan berhasil dimuat</p>
            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-emerald-700">
                <span class="font-semibold">{{ $importedItemCount }} item</span>
                <span class="text-emerald-400">·</span>
                <span>{{ \Carbon\Carbon::parse($initialTransactionDate)->translatedFormat('d M Y') }}</span>
                @if(!empty($importDraft['note_number']))
                <span class="text-emerald-400">·</span>
                <span>{{ $importDraft['note_number'] }}</span>
                @endif
                <span class="text-emerald-400">·</span>
                <span id="import-summary-total" class="font-bold">Rp {{ number_format((float)$initialGrandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
        <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-700">Review Mode</span>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         TWO-COLUMN LAYOUT: Main + Sidebar
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px] 2xl:grid-cols-[1fr_360px]">

        {{-- ── LEFT COLUMN ──────────────────────────────────── --}}
        <div class="space-y-5 min-w-0">

            {{-- ── SECTION 1: Info Transaksi ─── --}}
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                {{-- Header --}}
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-100 text-[11px] font-black text-indigo-600">1</span>
                        <h2 class="text-sm font-bold text-slate-800">Informasi Transaksi</h2>
                    </div>
                    <span class="rounded-full {{ isset($purchaseTransaction) ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }} px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest">
                        {{ isset($purchaseTransaction) ? 'Edit' : 'Baru' }}
                    </span>
                </div>

                <div class="p-5">
                    {{-- Row 1: 4 fields --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                        {{-- Tanggal --}}
                        <div>
                            <label class="mb-1.5 flex items-center gap-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                                Tanggal <span class="text-rose-400">*</span>
                            </label>
                            <input type="date" name="transaction_date" value="{{ $initialTransactionDate }}" required
                                   class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                        </div>

                        {{-- No. Nota --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-500">No. Nota</label>
                            <input type="text" name="note_number" value="{{ $noteNumberValue }}" placeholder="Nomor nota / invoice"
                                   class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                        </div>

                        {{-- Proyek --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-500">Proyek</label>
                            <select name="project_id"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                                <option value="">— Pilih proyek —</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ (string)$selectedProjectId === (string)$project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="mb-1.5 flex items-center gap-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                                Status <span class="text-rose-400">*</span>
                            </label>
                            <select name="status" required
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                                <option value="draft"     @selected($statusValue === 'draft')>Draft</option>
                                <option value="submitted" @selected($statusValue === 'submitted')>Submitted</option>
                                @if(auth()->user()->isAdmin())
                                <option value="posted"   @selected($statusValue === 'posted')>Posted</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Supplier --}}
                    <div class="mt-4">
                        <label class="mb-1.5 flex items-center gap-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                            Supplier <span class="text-rose-400">*</span>
                        </label>
                        <select name="supplier_id" id="supplier-select"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                            <option value="">— Pilih supplier —</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (string)$selectedSupplierId === (string)$supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Scan supplier info --}}
                        @if($scannedSupplierName)
                        <div class="mt-2" id="scan-supplier-wrapper">
                            @if($hasMatchedSupplier)
                            <div id="scan-supplier-success" class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="text-xs font-bold text-emerald-800">Supplier terdeteksi dari scan</p>
                                    <p class="text-xs text-emerald-700">{{ $scannedSupplierName }}</p>
                                </div>
                            </div>
                            @endif
                            @if($showMissingSupplierCard)
                            <div id="scan-supplier-missing" data-supplier-name="{{ $scannedSupplierName }}"
                                 class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5">
                                <div>
                                    <p class="text-xs font-bold text-amber-800">Supplier dari scan belum ada di daftar</p>
                                    <p id="scan-supplier-name-text" class="text-xs text-amber-700">{{ $scannedSupplierName }}</p>
                                </div>
                                <button type="button" id="btn-add-scan-supplier"
                                        class="shrink-0 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition-colors">
                                    + Tambah Supplier
                                </button>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Row 3: Catatan --}}
                    <div class="mt-4">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-500">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                                  class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">{{ $notesValue }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2: Item Pembelian ─── --}}
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                {{-- Header --}}
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 text-[11px] font-black text-emerald-600">2</span>
                        <h2 class="text-sm font-bold text-slate-800">Item Pembelian</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Mini stats --}}
                        <div class="hidden sm:flex items-center gap-3 text-xs">
                            <span class="text-slate-400">Item: <strong id="top-item-count" class="text-slate-700">{{ count($items) }}</strong></span>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-400">Scan: <strong id="top-import-count" class="text-slate-700">{{ $importedItemCount }}</strong></span>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-400">Total: <strong id="top-grand-total" class="text-indigo-600">Rp {{ number_format((float)$initialGrandTotal, 0, ',', '.') }}</strong></span>
                        </div>
                        <button type="button" id="btn-add-item"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition-colors shadow-sm">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Tambah Item
                        </button>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="flex items-start gap-2.5 border-b border-slate-100 bg-slate-50/70 px-5 py-2.5">
                    <svg class="h-4 w-4 shrink-0 text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Ketik <strong>kode material</strong> lalu klik <strong>Cari</strong> untuk auto-fill, atau pilih langsung dari dropdown.
                        Qty dan Harga akan otomatis menghitung Subtotal.
                    </p>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-slate-200">
                    <table class="min-w-[1300px] w-full text-sm" id="items-table">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60">
                                <th class="w-8 px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">#</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[150px]">Kode Material</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[240px]">Pilih dari Master</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[200px]">Nama Material <span class="text-rose-400">*</span></th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[100px]">Satuan <span class="text-rose-400">*</span></th>
                                <th class="px-3 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[90px]">Qty <span class="text-rose-400">*</span></th>
                                <th class="px-3 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[140px]">Harga <span class="text-rose-400">*</span></th>
                                <th class="px-3 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[150px]">Subtotal</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[180px]">Catatan</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400 w-16"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body" class="divide-y divide-slate-50">
                            @foreach($items as $index => $item)
                            @php
                                $rowSubtotal = (float)($item['subtotal'] ?? 0);
                                if ($rowSubtotal <= 0) $rowSubtotal = (float)($item['qty'] ?? 0) * (float)($item['price'] ?? 0);
                            @endphp
                            <tr class="item-row group align-middle hover:bg-indigo-50/30 transition-colors"
                                data-imported="{{ !empty($item['is_imported_scan']) ? '1' : '0' }}">

                                {{-- Row number --}}
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs font-bold text-slate-300">{{ $index + 1 }}</span>
                                    @if(!empty($item['is_imported_scan']))
                                    <div class="mt-1">
                                        <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700">OCR</span>
                                    </div>
                                    @endif
                                </td>

                                {{-- Kode --}}
                                <td class="px-3 py-3">
                                    <div class="flex gap-1.5">
                                        <input type="text" name="items[{{ $index }}][material_code]"
                                               value="{{ $item['material_code'] ?? '' }}"
                                               placeholder="e.g. BRC01"
                                               class="material-code-input h-9 w-full min-w-[90px] rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-mono font-semibold uppercase text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                        <button type="button"
                                                class="btn-lookup-code shrink-0 h-9 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-500 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                            Cari
                                        </button>
                                    </div>
                                </td>

                                {{-- Material select --}}
                                <td class="px-3 py-3">
                                    <select name="items[{{ $index }}][material_id]"
                                            class="material-select material-id-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                        <option value="">— Custom / pilih —</option>
                                        @foreach($materials as $material)
                                        <option value="{{ $material->id }}"
                                                data-code="{{ $material->code }}"
                                                data-name="{{ $material->name }}"
                                                data-unit="{{ $material->unit?->name }}"
                                                data-price="{{ $material->default_price }}"
                                                {{ (string)($item['material_id'] ?? '') === (string)$material->id ? 'selected' : '' }}>
                                            {{ $material->code }} — {{ $material->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Nama Material --}}
                                <td class="px-3 py-3">
                                    <input type="text" name="items[{{ $index }}][material_name_snapshot]"
                                           value="{{ $item['material_name_snapshot'] ?? '' }}"
                                           placeholder="Nama material"
                                           required
                                           class="material-name-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                </td>

                                {{-- Satuan --}}
                                <td class="px-3 py-3">
                                    <input type="text" name="items[{{ $index }}][unit_name_snapshot]"
                                           value="{{ $item['unit_name_snapshot'] ?? '' }}"
                                           placeholder="pcs / m2"
                                           required
                                           class="unit-name-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                </td>

                                {{-- Qty --}}
                                <td class="px-3 py-3">
                                    <input type="number" step="0.01" min="0.01"
                                           name="items[{{ $index }}][qty]"
                                           value="{{ $item['qty'] ?? 1 }}"
                                           required
                                           class="qty-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-right text-xs font-bold text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                </td>

                                {{-- Harga --}}
                                <td class="px-3 py-3">
                                    <input type="number" step="0.01" min="0"
                                           name="items[{{ $index }}][price]"
                                           value="{{ $item['price'] ?? 0 }}"
                                           required
                                           class="price-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-right text-xs font-bold text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                                </td>

                                {{-- Subtotal --}}
                                <td class="px-3 py-3">
                                    <input type="hidden" name="items[{{ $index }}][subtotal]" value="{{ $rowSubtotal }}" class="subtotal-hidden">
                                    <input type="text" value="Rp {{ number_format($rowSubtotal, 0, ',', '.') }}"
                                           class="subtotal-preview h-9 w-full rounded-lg border border-slate-200 bg-indigo-50/50 px-2.5 text-right text-xs font-black text-indigo-700"
                                           readonly>
                                </td>

                                {{-- Catatan --}}
                                <td class="px-3 py-3">
                                    <input type="text" name="items[{{ $index }}][notes]"
                                           value="{{ $item['notes'] ?? '' }}"
                                           placeholder="Opsional"
                                           class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-600 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-100 transition">
                                </td>

                                {{-- Hapus --}}
                                <td class="px-3 py-3 text-center">
                                    <button type="button"
                                            class="btn-remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors mx-auto">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        {{-- Grand Total row --}}
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50">
                                <td colspan="7" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-slate-500">Grand Total</td>
                                <td class="px-3 py-3 text-right">
                                    <span id="grand-total-text" class="text-base font-black text-indigo-700">
                                        Rp {{ number_format((float)$initialGrandTotal, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Add item footer --}}
                <div class="border-t border-slate-100 px-5 py-3">
                    <button type="button" id="btn-add-item-bottom"
                            onclick="document.getElementById('btn-add-item').click()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-500 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah baris item
                    </button>
                </div>
            </div>

        </div>{{-- /left column --}}

        {{-- ── SIDEBAR ──────────────────────────────────── --}}
        <div class="xl:sticky xl:top-5 xl:self-start">
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">

                {{-- Sidebar header --}}
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-slate-800">Ringkasan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Periksa sebelum menyimpan</p>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Date display --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-xs font-semibold text-slate-500">Tanggal</span>
                        <span id="summary-date-text" class="text-sm font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($initialTransactionDate)->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    {{-- Item count + total --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Total Item</p>
                            <p id="summary-item-count" class="mt-1 text-xl font-black text-slate-800">{{ count($items) }}</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-400">Grand Total</p>
                            <p id="sidebar-grand-total" class="mt-1 text-sm font-black text-indigo-700 leading-tight">
                                Rp {{ number_format((float)$initialGrandTotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Scan item count (hidden if 0) --}}
                    @if($importedItemCount > 0)
                    <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5">
                        <span class="text-xs font-semibold text-emerald-700">Item dari scan</span>
                        <span id="sidebar-item-count" class="text-sm font-black text-emerald-800">{{ $importedItemCount }}</span>
                    </div>
                    @endif

                    {{-- Attachments --}}
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Lampiran</p>

                        <div id="attachment-dropzone"
                             class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 text-center transition-all hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer"
                             onclick="document.getElementById('attachments-input').click()">
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-500">Klik atau drag & drop file</p>
                                <p class="text-[10px] text-slate-400">JPG, PNG, PDF · maks 5MB/file</p>
                            </div>
                            <input id="attachments-input" type="file" name="attachments[]" multiple
                                   accept=".jpg,.jpeg,.png,.webp,.pdf"
                                   class="hidden">
                        </div>

                        <div id="attachment-preview-list" class="mt-3 hidden grid grid-cols-1 gap-2"></div>

                        {{-- Existing attachments --}}
                        @if(isset($purchaseTransaction) && $purchaseTransaction->attachments->count())
                        <div class="mt-3 space-y-2">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Lampiran Tersimpan</p>
                            @foreach($purchaseTransaction->attachments as $attachment)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-semibold text-slate-700">{{ $attachment->file_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <a href="{{ $attachment->url }}" target="_blank"
                                       class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                    <button type="button"
                                            onclick="if(confirm('Hapus lampiran ini?')) document.getElementById('delete-attachment-{{ $attachment->id }}').submit();"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-rose-400 hover:bg-rose-50 hover:border-rose-200 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                @if(!isset($purchaseTransaction))
                {{-- Toko: barang datang bersama nota --}}
                <div class="border-t border-slate-100 p-4 space-y-3" x-data="{ paid: {{ old('paid_in_full') ? 'true' : 'false' }} }">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-3">
                        <input type="hidden" name="mark_received" value="0">
                        <input type="checkbox" name="mark_received" value="1" id="mark-received"
                               class="mt-0.5 h-4 w-4 rounded accent-emerald-600"
                               @checked(old('mark_received', config('store.auto_receive_default')))>
                        <span>
                            <span class="block text-sm font-bold text-emerald-800">Barang sudah diterima</span>
                            <span class="block text-[11px] text-emerald-700">Stok langsung bertambah tanpa membuat surat jalan terpisah.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3">
                        <input type="hidden" name="paid_in_full" value="0">
                        <input type="checkbox" name="paid_in_full" value="1" x-model="paid" class="mt-0.5 h-4 w-4 rounded accent-indigo-600">
                        <span>
                            <span class="block text-sm font-bold text-slate-800">Sudah dibayar lunas</span>
                            <span class="block text-[11px] text-slate-500">Tidak menjadi hutang ke supplier.</span>
                        </span>
                    </label>
                    <select name="payment_method" x-show="paid" x-cloak
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                @endif

                {{-- CTA Buttons --}}
                <div class="border-t border-slate-100 p-4 space-y-2.5">
                    <button type="submit" id="btn-submit-transaction"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm shadow-indigo-200 hover:bg-indigo-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18l9-12"/>
                        </svg>
                        {{ isset($purchaseTransaction) ? 'Simpan Perubahan' : 'Simpan Transaksi' }}
                    </button>
                    @if(!isset($purchaseTransaction))
                    <button type="button" id="btn-reset-form"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-100 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Reset Form
                    </button>
                    @endif
                    <a href="{{ route('purchase-transactions.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                </div>
            </div>
        </div>{{-- /sidebar --}}
    </div>

</div>{{-- /space-y-5 --}}

{{-- ══════════════════════════════════════════
     MODAL: Missing Material
══════════════════════════════════════════ --}}
<div id="missing-material-modal"
     class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-lg rounded-2xl border border-slate-100 bg-white shadow-2xl">
        <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Ada barang yang belum terdaftar</h3>
                <p class="text-sm text-slate-500 mt-0.5">Tambahkan sebagai barang baru agar stok &amp; harga jualnya tercatat? Barang baru masuk kategori "Dari Nota (perlu dicek)".</p>
            </div>
        </div>
        <div class="px-5 py-4">
            <div id="missing-material-list" class="space-y-2 max-h-60 overflow-y-auto"></div>
        </div>
        <div class="flex gap-2.5 border-t border-slate-100 px-5 py-4">
            <button type="button" id="btn-missing-material-no"
                    class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Simpan tanpa menambah
            </button>
            <button type="button" id="btn-missing-material-yes"
                    class="flex-1 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                Ya, tambah barang baru
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

const supplierSelect = document.getElementById('supplier-select');
const scanSupplierMissingCard = document.getElementById('scan-supplier-missing');
const addScanSupplierButton = document.getElementById('btn-add-scan-supplier');
const scanSupplierWrapper = document.getElementById('scan-supplier-wrapper');

async function quickAddSupplierFromScan() {
    if (!scanSupplierMissingCard || !supplierSelect) return;
    const supplierName = String(scanSupplierMissingCard.dataset.supplierName || '').trim();
    if (!supplierName) return;
    const originalText = addScanSupplierButton ? addScanSupplierButton.textContent : '';
    if (addScanSupplierButton) { addScanSupplierButton.disabled = true; addScanSupplierButton.textContent = 'Menambahkan...'; }
    try {
        const response = await fetch(window.quickStoreSupplierUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ name: supplierName }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Gagal menambahkan supplier.');
        let option = supplierSelect.querySelector(`option[value="${data.supplier.id}"]`);
        if (!option) { option = document.createElement('option'); option.value = data.supplier.id; option.textContent = data.supplier.name; supplierSelect.appendChild(option); }
        supplierSelect.value = String(data.supplier.id);
        if (scanSupplierMissingCard) scanSupplierMissingCard.remove();
        if (scanSupplierWrapper) {
            const successCard = document.createElement('div');
            successCard.className = 'flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5';
            successCard.id = 'scan-supplier-success';
            successCard.innerHTML = `<svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><div><p class="text-xs font-bold text-emerald-800">Supplier berhasil ditambahkan</p><p class="text-xs text-emerald-700">${data.supplier.name}</p></div>`;
            scanSupplierWrapper.appendChild(successCard);
        }
    } catch (error) {
        alert(error.message || 'Gagal menambahkan supplier.');
    } finally {
        if (addScanSupplierButton) { addScanSupplierButton.disabled = false; addScanSupplierButton.textContent = originalText || '+ Tambah Supplier'; }
    }
}
if (addScanSupplierButton) addScanSupplierButton.addEventListener('click', quickAddSupplierFromScan);

   const form = document.getElementById('purchase-transaction-form');
const submitButton = document.getElementById('btn-submit-transaction');
const autoCreateMissingMaterialsInput = document.getElementById('auto-create-missing-materials');
const missingMaterialModal = document.getElementById('missing-material-modal');
const missingMaterialList = document.getElementById('missing-material-list');
const missingMaterialYes = document.getElementById('btn-missing-material-yes');
const missingMaterialNo = document.getElementById('btn-missing-material-no');

function normalizeMaterialCode(code) { return String(code || '').toUpperCase().replace(/[^A-Z0-9]/g, ''); }
function getMasterMaterialCodeSet() { return new Set(materialOptions.map(item => normalizeMaterialCode(item.code || '')).filter(Boolean)); }

function collectMissingMaterialRows() {
    const masterCodeSet = getMasterMaterialCodeSet();
    const missingMap = new Map();
    itemsBody.querySelectorAll('.item-row').forEach(function (row, index) {
        const codeInput = row.querySelector('.material-code-input');
        const materialIdInput = row.querySelector('.material-id-input');
        const nameInput = row.querySelector('.material-name-input');
        const unitInput = row.querySelector('.unit-name-input');
        const priceInput = row.querySelector('.price-input');
        const materialId = String(materialIdInput?.value || '').trim();
        const rawCode = String(codeInput?.value || '').trim();
        const normalizedCode = normalizeMaterialCode(rawCode);
        const name = String(nameInput?.value || '').trim();
        if (materialId || !name) return;
        if (normalizedCode && masterCodeSet.has(normalizedCode)) return;
        const key = normalizedCode || ('NAME:' + name.toLowerCase());
        if (!missingMap.has(key)) {
            missingMap.set(key, { code: rawCode || '(kode otomatis)', material_name_snapshot: name, unit_name_snapshot: String(unitInput?.value || '').trim(), price: Number(priceInput?.value || 0), row: index + 1 });
        }
    });
    return Array.from(missingMap.values());
}

function openMissingMaterialModal(items) {
    if (!missingMaterialModal || !missingMaterialList) return;
    missingMaterialList.innerHTML = items.map(function (item) {
        return `<div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5"><div class="text-xs font-black text-amber-800">${item.code}</div><div class="text-[11px] text-amber-700">Baris ${item.row} · ${item.material_name_snapshot || '-'} · ${item.unit_name_snapshot || '-'}</div></div>`;
    }).join('');
    missingMaterialModal.classList.remove('hidden');
    missingMaterialModal.classList.add('flex');
}
function closeMissingMaterialModal() { if (!missingMaterialModal) return; missingMaterialModal.classList.add('hidden'); missingMaterialModal.classList.remove('flex'); }
if (submitButton && form) {
    submitButton.addEventListener('click', function (e) {
        e.preventDefault();
        const missingItems = collectMissingMaterialRows();
        if (!missingItems.length) { if (autoCreateMissingMaterialsInput) autoCreateMissingMaterialsInput.value = '0'; form.submit(); return; }
        openMissingMaterialModal(missingItems);
    });
}
if (missingMaterialNo && form) {
    missingMaterialNo.addEventListener('click', function () { if (autoCreateMissingMaterialsInput) autoCreateMissingMaterialsInput.value = '0'; closeMissingMaterialModal(); form.submit(); });
}
if (missingMaterialYes && form) {
    missingMaterialYes.addEventListener('click', function () { if (autoCreateMissingMaterialsInput) autoCreateMissingMaterialsInput.value = '1'; closeMissingMaterialModal(); form.submit(); });
}
if (missingMaterialModal) missingMaterialModal.addEventListener('click', function (e) { if (e.target === missingMaterialModal) closeMissingMaterialModal(); });

    const itemsBody = document.getElementById('items-body');
    const addButton = document.getElementById('btn-add-item');
    const grandTotalText = document.getElementById('grand-total-text');
    const topGrandTotal = document.getElementById('top-grand-total');
    const sidebarGrandTotal = document.getElementById('sidebar-grand-total');
    const importSummaryTotal = document.getElementById('import-summary-total');
    const summaryItemCount = document.getElementById('summary-item-count');
    const topItemCount = document.getElementById('top-item-count');
    const sidebarItemCount = document.getElementById('sidebar-item-count');
    const topImportCount = document.getElementById('top-import-count');
    const materialOptions = @json($materialOptions);

    const dateInput = document.querySelector('input[name="transaction_date"]');
    const summaryDateText = document.getElementById('summary-date-text');

    const attachmentInput = document.getElementById('attachments-input');
    const attachmentDropzone = document.getElementById('attachment-dropzone');
    const attachmentPreviewList = document.getElementById('attachment-preview-list');

    function formatRupiah(number) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(number || 0)); }
    function toNumber(value) { const num = Number(value || 0); return Number.isFinite(num) ? num : 0; }
    function formatDateIndo(value) {
        if (!value) return '-';
        const date = new Date(value + 'T00:00:00');
        if (isNaN(date.getTime())) return value;
        return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    }
    function updateSummaryDate() { if (summaryDateText && dateInput) summaryDateText.textContent = formatDateIndo(dateInput.value); }
    function countImportedRows() { return itemsBody.querySelectorAll('.item-row[data-imported="1"]').length; }
    function countAllRows() { return itemsBody.querySelectorAll('.item-row').length; }
    function updateCountWidgets() {
        const totalRows = countAllRows(); const importedRows = countImportedRows();
        if (summaryItemCount) summaryItemCount.textContent = totalRows;
        if (topItemCount) topItemCount.textContent = totalRows;
        if (sidebarItemCount) sidebarItemCount.textContent = totalRows;
        if (topImportCount) topImportCount.textContent = importedRows;
    }
    function syncGrandTotalDisplays(total) {
        const formatted = formatRupiah(total);
        if (grandTotalText) grandTotalText.textContent = formatted;
        if (topGrandTotal) topGrandTotal.textContent = formatted;
        if (sidebarGrandTotal) sidebarGrandTotal.textContent = formatted;
        if (importSummaryTotal) importSummaryTotal.textContent = formatted;
    }
    function recalcGrandTotal() {
        let total = 0;
        itemsBody.querySelectorAll('.item-row').forEach(function (row) {
            const subtotalHidden = row.querySelector('.subtotal-hidden');
            const subtotalPreview = row.querySelector('.subtotal-preview');
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            let subtotal = toNumber(subtotalHidden?.value || 0);
            if (subtotal <= 0) { const qty = toNumber(qtyInput?.value || 0); const price = toNumber(priceInput?.value || 0); subtotal = qty * price; if (subtotalHidden) subtotalHidden.value = subtotal; }
            if (subtotalPreview) subtotalPreview.value = formatRupiah(subtotal);
            total += subtotal;
        });
        syncGrandTotalDisplays(total);
        updateCountWidgets();
    }
    function recalcRow(row, forceRecalculate = false) {
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const subtotalHidden = row.querySelector('.subtotal-hidden');
        const subtotalPreview = row.querySelector('.subtotal-preview');
        const qty = toNumber(qtyInput?.value || 0);
        const price = toNumber(priceInput?.value || 0);
        let subtotal = toNumber(subtotalHidden?.value || 0);
        if (forceRecalculate || subtotal <= 0) { subtotal = qty * price; if (subtotalHidden) subtotalHidden.value = subtotal; }
        if (subtotalPreview) subtotalPreview.value = formatRupiah(subtotal);
        recalcGrandTotal();
    }
    function fillRowFromMaterial(row, material) {
        const codeInput = row.querySelector('.material-code-input');
        const materialSelect = row.querySelector('.material-select');
        const materialNameInput = row.querySelector('.material-name-input');
        const unitNameInput = row.querySelector('.unit-name-input');
        const priceInput = row.querySelector('.price-input');
        if (codeInput) codeInput.value = material.code || '';
        if (materialSelect) materialSelect.value = material.id || '';
        if (materialNameInput) materialNameInput.value = material.name || '';
        if (unitNameInput) unitNameInput.value = material.unit || '';
        if (priceInput && (!priceInput.value || toNumber(priceInput.value) === 0)) priceInput.value = material.price ?? 0;
        recalcRow(row);
    }
    function findMaterialByCode(code) {
        const normalized = String(code || '').trim().toLowerCase();
        if (!normalized) return null;
        return materialOptions.find(function (item) { return String(item.code || '').trim().toLowerCase() === normalized; }) || null;
    }
    function buildMaterialOptionsHtml() {
        let options = '<option value="">— Custom / pilih —</option>';
        materialOptions.forEach(function (item) {
            options += `<option value="${item.id}" data-code="${item.code??''}" data-name="${item.name??''}" data-unit="${item.unit??''}" data-price="${item.price??0}">${item.code??''} — ${item.name??''}</option>`;
        });
        return options;
    }
    function createRow(index) {
        const row = document.createElement('tr');
        row.className = 'item-row group align-middle hover:bg-indigo-50/30 transition-colors';
        row.dataset.imported = '0';
        row.innerHTML = `
            <td class="px-3 py-3 text-center"><span class="text-xs font-bold text-slate-300">${index+1}</span></td>
            <td class="px-3 py-3">
                <div class="flex gap-1.5">
                    <input type="text" name="items[${index}][material_code]" class="material-code-input h-9 w-full min-w-[90px] rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-mono font-semibold uppercase text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" placeholder="e.g. BRC01">
                    <button type="button" class="btn-lookup-code shrink-0 h-9 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-500 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">Cari</button>
                </div>
            </td>
            <td class="px-3 py-3">
                <select name="items[${index}][material_id]" class="material-select material-id-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition">
                    ${buildMaterialOptionsHtml()}
                </select>
            </td>
            <td class="px-3 py-3"><input type="text" name="items[${index}][material_name_snapshot]" class="material-name-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" placeholder="Nama material" required></td>
            <td class="px-3 py-3"><input type="text" name="items[${index}][unit_name_snapshot]" class="unit-name-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-medium text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" placeholder="pcs" required></td>
            <td class="px-3 py-3"><input type="number" step="0.01" min="0.01" name="items[${index}][qty]" value="1" class="qty-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-right text-xs font-bold text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" required></td>
            <td class="px-3 py-3"><input type="number" step="0.01" min="0" name="items[${index}][price]" value="0" class="price-input h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-right text-xs font-bold text-slate-700 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" required></td>
            <td class="px-3 py-3">
                <input type="hidden" name="items[${index}][subtotal]" value="0" class="subtotal-hidden">
                <input type="text" value="${formatRupiah(0)}" class="subtotal-preview h-9 w-full rounded-lg border border-slate-200 bg-indigo-50/50 px-2.5 text-right text-xs font-black text-indigo-700" readonly>
            </td>
            <td class="px-3 py-3"><input type="text" name="items[${index}][notes]" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-600 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-100 transition" placeholder="Opsional"></td>
            <td class="px-3 py-3 text-center">
                <button type="button" class="btn-remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors mx-auto">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </td>`;
        return row;
    }

    function renderAttachmentPreviews(files) {
        if (!attachmentPreviewList) return;
        attachmentPreviewList.innerHTML = '';
        if (!files || !files.length) { attachmentPreviewList.classList.add('hidden'); return; }
        attachmentPreviewList.classList.remove('hidden');
        Array.from(files).forEach(function(file) {
            const isImage = file.type.startsWith('image/');
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            const item = document.createElement('div');
            item.className = 'flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 py-2';
            let icon = isPdf
                ? `<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-[10px] font-black text-rose-600">PDF</div>`
                : `<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></div>`;
            if (isImage) { const url = URL.createObjectURL(file); icon = `<img src="${url}" class="h-8 w-8 shrink-0 rounded-lg object-cover border border-slate-200" alt="">`; }
            item.innerHTML = `${icon}<div class="min-w-0"><p class="truncate text-[11px] font-semibold text-slate-700">${file.name}</p><p class="text-[10px] text-slate-400">${(file.size/1024/1024).toFixed(2)} MB</p></div>`;
            attachmentPreviewList.appendChild(item);
        });
    }

    function bindRow(row) {
        const codeInput = row.querySelector('.material-code-input');
        const lookupButton = row.querySelector('.btn-lookup-code');
        const materialSelect = row.querySelector('.material-select');
        const materialNameInput = row.querySelector('.material-name-input');
        const unitNameInput = row.querySelector('.unit-name-input');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const removeButton = row.querySelector('.btn-remove-item');

        function lookupByCode() {
            const code = codeInput?.value || '';
            const found = findMaterialByCode(code);
            if (!code.trim()) return;
            if (!found) { alert('Kode material tidak ditemukan.'); return; }
            fillRowFromMaterial(row, found);
        }
        if (lookupButton) lookupButton.addEventListener('click', lookupByCode);
        if (codeInput) {
            codeInput.addEventListener('blur', lookupByCode);
            codeInput.addEventListener('input', function () { this.value = this.value.toUpperCase(); });
        }
        if (materialSelect) {
            materialSelect.addEventListener('change', function () {
                const selectedValue = this.value;
                if (!selectedValue) return;
                const found = materialOptions.find(function (item) { return String(item.id) === String(selectedValue); });
                if (!found) return;
                if (codeInput) codeInput.value = found.code || '';
                if (materialNameInput) materialNameInput.value = found.name || '';
                if (unitNameInput) unitNameInput.value = found.unit || '';
                if (priceInput && (!priceInput.value || toNumber(priceInput.value) === 0)) priceInput.value = found.price ?? 0;
                recalcRow(row);
            });
        }
        if (qtyInput) qtyInput.addEventListener('input', function () { row.dataset.imported = '0'; recalcRow(row, true); });
        if (priceInput) priceInput.addEventListener('input', function () { row.dataset.imported = '0'; recalcRow(row, true); });
        if (removeButton) {
            removeButton.addEventListener('click', function () {
                const rows = itemsBody.querySelectorAll('.item-row');
                if (rows.length <= 1) { alert('Minimal harus ada 1 item.'); return; }
                row.remove(); reindexRows(); recalcGrandTotal();
            });
        }
        recalcRow(row);
    }

    function reindexRows() {
        itemsBody.querySelectorAll('.item-row').forEach(function (row, index) {
            row.querySelector('td:first-child span') && (row.querySelector('td:first-child span').textContent = index + 1);
            row.querySelectorAll('input, select, textarea').forEach(function (field) {
                if (field.name) field.name = field.name.replace(/items\[\d+\]/g, `items[${index}]`);
            });
        });
    }

    addButton.addEventListener('click', function () {
        const index = itemsBody.querySelectorAll('.item-row').length;
        const row = createRow(index);
        itemsBody.appendChild(row);
        bindRow(row);
        reindexRows();
        recalcGrandTotal();
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    itemsBody.querySelectorAll('.item-row').forEach(function (row) { bindRow(row); });

    if (dateInput) { dateInput.addEventListener('change', updateSummaryDate); updateSummaryDate(); }

    if (attachmentInput) attachmentInput.addEventListener('change', function () { renderAttachmentPreviews(this.files); });

    if (attachmentDropzone && attachmentInput) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            attachmentDropzone.addEventListener(eventName, function (e) { e.preventDefault(); e.stopPropagation(); attachmentDropzone.classList.add('border-indigo-400', 'bg-indigo-50/50'); });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            attachmentDropzone.addEventListener(eventName, function (e) { e.preventDefault(); e.stopPropagation(); attachmentDropzone.classList.remove('border-indigo-400', 'bg-indigo-50/50'); });
        });
        attachmentDropzone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            if (!dt || !dt.files || !dt.files.length) return;
            attachmentInput.files = dt.files;
            renderAttachmentPreviews(dt.files);
        });
    }

    recalcGrandTotal();
    updateCountWidgets();

    const resetButton = document.getElementById('btn-reset-form');
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (!confirm('Reset semua isian form? Data yang belum disimpan akan hilang.')) return;

            const today = new Date().toISOString().split('T')[0];
            const dateField = document.querySelector('input[name="transaction_date"]');
            const noteNumberField = document.querySelector('input[name="note_number"]');
            const projectField = document.querySelector('select[name="project_id"]');
            const statusField = document.querySelector('select[name="status"]');
            const supplierField = document.getElementById('supplier-select');
            const notesField = document.querySelector('textarea[name="notes"]');

            if (dateField) { dateField.value = today; updateSummaryDate(); }
            if (noteNumberField) noteNumberField.value = '';
            if (projectField) projectField.value = '';
            if (statusField) statusField.value = 'draft';
            if (supplierField) supplierField.value = '';
            if (notesField) notesField.value = '';

            const scanWrapper = document.getElementById('scan-supplier-wrapper');
            if (scanWrapper) scanWrapper.remove();

            const importBanner = document.querySelector('.border-emerald-200.bg-emerald-50');
            if (importBanner && importBanner.closest('.flex.items-center.gap-4')) {
                importBanner.closest('.flex.items-center.gap-4').remove();
            }

            itemsBody.innerHTML = '';
            const newRow = createRow(0);
            itemsBody.appendChild(newRow);
            bindRow(newRow);
            recalcGrandTotal();
            updateCountWidgets();
        });
    }
});
</script>

<script>
    window.quickStoreSupplierUrl = @json(route('suppliers.quick-store-from-scan'));
    window.csrfToken = @json(csrf_token());
</script>
