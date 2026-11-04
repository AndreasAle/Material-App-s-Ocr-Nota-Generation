@extends('layouts.app')

@section('content')
<x-page-header title="Buat RAB Baru" subtitle="Rencanakan anggaran biaya material untuk proyek" />

<form method="POST" action="{{ route('rab-plans.store') }}">
    @csrf
    @include('rab-plans.partials.form')
</form>
@endsection

@stack('scripts')
