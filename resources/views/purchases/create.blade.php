@extends('layouts.app')

@section('content')
<x-page-header title="Tambah Transaksi Pembelian" subtitle="Input pembelian material dan lampiran nota." />

<x-data-card class="p-5">
    <form id="purchase-transaction-form" method="POST" action="{{ route('purchase-transactions.store') }}" enctype="multipart/form-data">
        @include('purchases.partials.form')
    </form>
</x-data-card>
@endsection