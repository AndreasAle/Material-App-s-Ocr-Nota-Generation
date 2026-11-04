@extends('layouts.app')
@section('content')
<x-page-header title="Edit Supplier" subtitle="Perbarui data supplier." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @method('PUT')
        @include('master.suppliers._form')
    </form>
</x-data-card>
@endsection