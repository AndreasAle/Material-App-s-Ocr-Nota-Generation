@extends('layouts.app')
@section('content')
<x-page-header title="Edit Material" subtitle="Perbarui data material." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('materials.update', $material) }}">
        @method('PUT')
        @include('master.materials._form')
    </form>
</x-data-card>
@endsection