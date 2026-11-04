@extends('layouts.app')
@section('content')
<x-page-header title="Tambah Satuan" subtitle="Buat satuan baru untuk material." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('units.store') }}">
        @include('master.units._form')
    </form>
</x-data-card>
@endsection