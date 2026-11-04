<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt_no'                          => 'required|string|max:100',
            'receipt_date'                        => 'required|date',
            'received_by'                         => 'required|string|max:255',
            'notes'                               => 'nullable|string|max:1000',
            'items'                               => 'required|array|min:1',
            'items.*.purchase_transaction_item_id' => 'nullable|exists:purchase_transaction_items,id',
            'items.*.material_name_snapshot'      => 'required|string|max:255',
            'items.*.unit_name_snapshot'          => 'required|string|max:100',
            'items.*.qty_ordered'                 => 'required|numeric|gt:0',
            'items.*.qty_received'                => 'required|numeric|gte:0',
            'items.*.notes'                       => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'receipt_no'   => 'nomor surat jalan',
            'receipt_date' => 'tanggal penerimaan',
            'received_by'  => 'nama penerima',
            'items'        => 'item barang',
        ];
    }
}
