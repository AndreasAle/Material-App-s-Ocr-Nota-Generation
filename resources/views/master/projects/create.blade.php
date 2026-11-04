@extends('layouts.app')
@section('content')
<x-page-header title="Tambah Proyek" subtitle="Masukkan data proyek baru." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('projects.store') }}">
        @include('master.projects._form')
    </form>
</x-data-card>
@endsection