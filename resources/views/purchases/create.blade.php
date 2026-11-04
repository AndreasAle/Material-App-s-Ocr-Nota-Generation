@extends('layouts.app')

@section('content')
<x-page-header title="Catat Pembelian" subtitle="Cek isi nota, tentukan sudah dibayar atau belum, lalu simpan." :back="route('purchase-transactions.index')" />

<div class="lg:rounded-2xl lg:border lg:border-charcoal-200 lg:bg-white lg:p-5 lg:shadow-sm">
    <form id="purchase-transaction-form" method="POST" action="{{ route('purchase-transactions.store') }}" enctype="multipart/form-data">
        @include('purchases.partials.form')
    </form>
</div>
@endsection