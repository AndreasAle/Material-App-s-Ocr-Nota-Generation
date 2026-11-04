@extends('layouts.app')

@section('content')
<x-page-header title="Edit Transaksi Pembelian" subtitle="Perbarui data transaksi pembelian." />

<x-data-card class="p-5">
    <form id="purchase-transaction-form" method="POST" action="{{ route('purchase-transactions.update', $purchaseTransaction) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('purchases.partials.form')
    </form>
</x-data-card>

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