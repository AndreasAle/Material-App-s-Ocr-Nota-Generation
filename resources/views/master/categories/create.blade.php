@extends('layouts.app')

@section('content')
<x-page-header title="Tambah Kategori Material" subtitle="Buat kategori baru untuk pengelompokan material." />

<x-data-card class="p-5">
    <form method="POST" action="{{ route('material-categories.store') }}">
        @include('master.categories._form')
    </form>
</x-data-card>
@endsection