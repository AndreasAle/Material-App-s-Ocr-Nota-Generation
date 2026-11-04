@extends('layouts.app')
@section('content')
<x-page-header title="Edit Proyek" subtitle="Perbarui data proyek." />
<x-data-card class="p-5">
    <form method="POST" action="{{ route('projects.update', $project) }}">
        @method('PUT')
        @include('master.projects._form')
    </form>
</x-data-card>
@endsection