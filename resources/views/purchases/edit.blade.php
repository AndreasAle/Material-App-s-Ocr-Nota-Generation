@extends('layouts.app')

@section('content')
<x-page-header title="Ubah Pembelian" subtitle="Perbarui data pembelian ini." :back="route('purchase-transactions.show', $purchaseTransaction)" />

<div class="lg:rounded-2xl lg:border lg:border-charcoal-200 lg:bg-white lg:p-5 lg:shadow-sm">
    <form id="purchase-transaction-form" method="POST" action="{{ route('purchase-transactions.update', $purchaseTransaction) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('purchases.partials.form')
    </form>
</div>

@if(isset($purchaseTransaction) && $purchaseTransaction->attachments->count())
    @foreach($purchaseTransaction->attachments as $attachment)
        <form
            id="delete-attachment-{{ $attachment->id }}"
            action="{{ route('purchase-transactions.attachments.destroy', $attachment) }}"
            method="POST"
            class="hidden"
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
@endsection