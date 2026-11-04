@extends('layouts.app')
@section('content')

<x-page-header :title="$customer->exists ? 'Edit Pelanggan' : 'Pelanggan Baru'">
    <x-slot name="actions">
        <a href="{{ route('store.customers.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-xl">
    <x-data-card>
        <form method="POST" action="{{ $customer->exists ? route('store.customers.update', $customer) : route('store.customers.store') }}" class="space-y-4 px-5 py-5">
            @csrf
            @if($customer->exists) @method('PUT') @endif
            <div>
                <label class="form-label-required">Nama</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-input" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">No. WhatsApp</label>
                <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="08…" class="form-input">
                <p class="mt-1 text-xs text-slate-400">Dipakai untuk kirim struk &amp; pengingat bon.</p>
            </div>
            <div>
                <label class="form-label">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Limit bon (Rp)</label>
                <input type="number" name="credit_limit" min="0" step="1000" value="{{ old('credit_limit', $customer->credit_limit) }}" placeholder="Kosong = tanpa limit" class="form-input">
            </div>
            <div>
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes', $customer->notes) }}</textarea>
            </div>
            @if($customer->exists)
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->is_active))> Aktif</label>
            @endif
            <div class="flex justify-end"><button class="btn-primary">Simpan</button></div>
        </form>
    </x-data-card>
</div>
@endsection
