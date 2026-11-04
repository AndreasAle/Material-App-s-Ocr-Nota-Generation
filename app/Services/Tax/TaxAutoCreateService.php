<?php

namespace App\Services\Tax;

use App\Models\PurchaseTransaction;
use App\Models\TaxPpnMasukan;
use Carbon\Carbon;

/**
 * TaxAutoCreateService
 *
 * Otomatis buat draft PPN Masukan saat transaksi pembelian disimpan.
 * Record ini perlu dilengkapi oleh user (isi no_faktur & tanggal_faktur).
 */
class TaxAutoCreateService
{
    /**
     * Buat draft TaxPpnMasukan dari transaksi pembelian.
     * Tidak akan buat duplikat jika sudah ada record untuk transaksi ini.
     *
     * @param  PurchaseTransaction  $tx
     * @param  int                  $userId
     * @return TaxPpnMasukan|null
     */
    public function createPpnMasukanDraft(PurchaseTransaction $tx, int $userId): ?TaxPpnMasukan
    {
        // Toko perorangan (Mode Toko) tidak mengelola PPN Masukan
        if (config('store.mode')) {
            return null;
        }

        // Jangan buat duplikat
        if (TaxPpnMasukan::where('purchase_transaction_id', $tx->id)->exists()) {
            return null;
        }

        // Hanya buat jika ada nilai transaksi
        if ((float) $tx->grand_total <= 0) {
            return null;
        }

        // Hitung DPP dari grand_total (asumsi harga sudah termasuk PPN 12%)
        $tarifFactor = 1 + (TaxCalculatorService::TARIF_PPN / 100); // 1.12
        $dpp         = (int) round((float) $tx->grand_total / $tarifFactor);
        $ppn         = (int) round((float) $tx->grand_total - $dpp);

        // Ambil data supplier (sudah ter-load dari store())
        $supplier = $tx->relationLoaded('supplier') ? $tx->supplier : $tx->supplier()->first();

        return TaxPpnMasukan::create([
            'purchase_transaction_id' => $tx->id,
            'supplier_id'             => $tx->supplier_id,
            'no_faktur'               => null,
            'tanggal_faktur'          => \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d'),
            'npwp_penjual'            => $supplier?->npwp ?? null,
            'nama_penjual'            => $supplier?->name ?? ('Tx ' . $tx->transaction_no),
            'dpp'                     => $dpp,
            'ppn'                     => $ppn,
            'periode'                 => Carbon::parse($tx->transaction_date)->format('Y-m'),
            'status'                  => 'normal',
            'dapat_dikreditkan'       => true,
            'keterangan'              => 'Draft otomatis dari transaksi ' . $tx->transaction_no,
            'created_by'              => $userId,
        ]);
    }
}
