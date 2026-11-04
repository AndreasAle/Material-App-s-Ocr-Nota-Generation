@extends('layouts.app')
@section('content')

<x-page-header title="Tambah Subkontraktor" subtitle="Data subkontraktor baru">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<x-data-card class="max-w-2xl">
    <form method="POST" action="{{ route('subcontractors.store') }}" class="p-5 space-y-4">
        @csrf
        @include('subcontractors.partials.form')
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('subcontractors.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</x-data-card>
@endsection
