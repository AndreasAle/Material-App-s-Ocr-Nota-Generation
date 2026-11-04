@extends('layouts.app')

@section('content')
<x-page-header title="Edit Kategori Material" subtitle="Perbarui data kategori material." />

<x-data-card class="p-5">
    <form method="POST" action="{{ route('material-categories.update', $materialCategory) }}">
        @method('PUT')
        @include('master.categories._form')
    </form>
</x-data-card>
@endsection