@extends('layouts.app')
@section('content')
<x-page-header title="Edit Satuan" subtitle="Perbarui data satuan." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('units.update', $unit) }}">
        @method('PUT')
        @include('master.units._form')
    </form>
</x-data-card>
@endsection