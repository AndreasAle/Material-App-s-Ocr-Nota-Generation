@extends('layouts.app')

@section('content')
<x-page-header title="Edit RAB" subtitle="{{ $rabPlan->name }}" />

<form method="POST" action="{{ route('rab-plans.update', $rabPlan) }}">
    @csrf @method('PUT')
    @include('rab-plans.partials.form')
</form>
@endsection

@stack('scripts')
