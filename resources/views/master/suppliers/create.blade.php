@extends('layouts.app')
@section('content')
<x-page-header title="Tambah Supplier" subtitle="Masukkan data supplier baru." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('suppliers.store') }}">
        @include('master.suppliers._form')
    </form>
</x-data-card>
@endsection