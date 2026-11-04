@extends('layouts.app')
@section('content')

<x-page-header title="Edit Subkontraktor" subtitle="{{ $subcontractor->name }}">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.show', $subcontractor) }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<x-data-card class="max-w-2xl">
    <form method="POST" action="{{ route('subcontractors.update', $subcontractor) }}" class="p-5 space-y-4">
        @csrf @method('PUT')
        @include('subcontractors.partials.form')
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('subcontractors.show', $subcontractor) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</x-data-card>
@endsection
