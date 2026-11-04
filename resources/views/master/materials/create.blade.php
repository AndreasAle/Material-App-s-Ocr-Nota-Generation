@extends('layouts.app')
@section('content')
<x-page-header title="Tambah Material" subtitle="Masukkan data material baru." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('materials.store') }}">
        @include('master.materials._form')
    </form>
</x-data-card>
@endsection