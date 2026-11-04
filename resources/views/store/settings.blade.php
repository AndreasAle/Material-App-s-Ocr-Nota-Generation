@extends('layouts.app')
@section('content')

<x-page-header title="Profil Toko" subtitle="Data ini tampil di struk & dipakai untuk saran harga jual dan tempo bon." :back="route('store.dashboard')" />

<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('store.settings.update') }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="card space-y-4 p-4 sm:p-5">
            <p class="section-label">Identitas toko</p>
            <div>
                <label class="form-label-required">Nama toko</label>
                <input type="text" name="name" value="{{ old('name', $values['name']) }}" class="form-input" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $values['address']) }}" class="form-input" placeholder="Jl. ...">
            </div>
            <div>
                <label class="form-label">No. Telepon / WhatsApp toko</label>
                <input type="tel" name="phone" value="{{ old('phone', $values['phone']) }}" class="form-input" placeholder="08...">
            </div>
            <div>
                <label class="form-label">Tulisan di bawah struk</label>
                <input type="text" name="footer" value="{{ old('footer', $values['footer']) }}" class="form-input">
            </div>
        </div>

        <div class="card space-y-4 p-4 sm:p-5">
            <p class="section-label">Aturan dagang</p>
            <div>
                <label class="form-label-required">Untung default (%)</label>
                <input type="number" name="default_margin" min="0" max="500" step="0.5" value="{{ old('default_margin', $values['default_margin']) }}" class="form-input" required>
                <p class="mt-1 text-xs text-charcoal-500">Dipakai untuk saran harga jual: harga beli + {{ $values['default_margin'] }}%.</p>
            </div>
            <div>
                <label class="form-label-required">Tempo bon pelanggan (hari)</label>
                <input type="number" name="credit_days" min="1" max="365" value="{{ old('credit_days', $values['credit_days']) }}" class="form-input" required>
                <p class="mt-1 text-xs text-charcoal-500">Bon baru jatuh tempo setelah sekian hari, lalu diingatkan lewat WhatsApp.</p>
            </div>
        </div>

        <button class="btn-primary w-full sm:w-auto">Simpan Profil Toko</button>
    </form>
</div>
@endsection
