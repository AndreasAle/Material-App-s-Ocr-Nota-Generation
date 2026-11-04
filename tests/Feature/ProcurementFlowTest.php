<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Services\Stock\StockMovementService;

/**
 * Alur lanjutan: approval, edit, surat jalan, pembayaran, PO, retur, invoice, stok, MR.
 */
class ProcurementFlowTest extends BusinessFlowTest
{
    private function stockOf(int $materialId): float
    {
        return (float) (app(StockMovementService::class)->currentStockAll()[$materialId] ?? 0);
    }

    private function txPayload(array $ctx, float $qty = 10, float $price = 65000): array
    {
        return [
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $ctx['supplier']->id, 'project_id' => $ctx['project']->id,
            'status' => 'posted',
            'items' => [[
                'material_id' => $ctx['material']->id, 'material_name_snapshot' => $ctx['material']->name,
                'unit_name_snapshot' => 'Sak', 'qty' => $qty, 'price' => $price,
            ]],
        ];
    }

    public function test_approval_reject_resubmit_and_edit_resets_approval(): void
    {
        $ctx = $this->seedFlow();
        $this->post('/purchase-transactions', $this->txPayload($ctx, 5))->assertSessionHasNoErrors();
        $tx = PurchaseTransaction::latest('id')->first();

        $this->assertSame('unpaid', $tx->payment_status);
        $this->assertEquals(325000, (float) $tx->remaining_amount);

        $this->post(route('purchase-transactions.submit-approval', $tx));
        $this->post(route('purchase-transactions.reject', $tx), ['rejection_reason' => 'Harga terlalu mahal']);
        $this->assertSame('rejected', $tx->fresh()->approval_status);

        // Transaksi ditolak tidak bisa dibayar & tidak masuk hutang
        $this->post(route('supplier-payments.store', $tx), ['payment_date' => now()->toDateString(), 'amount' => 1000, 'payment_method' => 'cash'])
            ->assertSessionHas('error');

        // Diajukan ulang setelah ditolak
        $this->post(route('purchase-transactions.submit-approval', $tx))->assertSessionMissing('error');
        $this->assertSame('pending', $tx->fresh()->approval_status);
        $this->post(route('purchase-transactions.approve', $tx));
        $this->assertSame('approved', $tx->fresh()->approval_status);

        // Edit transaksi yang sudah disetujui → kembali draft
        $this->put(route('purchase-transactions.update', $tx), $this->txPayload($ctx, 6))->assertSessionHasNoErrors();
        $tx->refresh();
        $this->assertSame('draft', $tx->approval_status);
        $this->assertEquals(390000, (float) $tx->remaining_amount);
    }

    public function test_goods_receipt_guards_and_edit_delete_locks(): void
    {
        $ctx = $this->seedFlow();
        $tx = $ctx['tx']; // sudah diterima penuh 10 sak di seedFlow
        $item = $tx->items->first();

        $this->post("/purchase-transactions/{$tx->id}/goods-receipts", [
            'receipt_no' => 'SJ-002', 'receipt_date' => now()->toDateString(), 'received_by' => 'Mandor',
            'items' => [[
                'purchase_transaction_item_id' => $item->id, 'material_name_snapshot' => $item->material_name_snapshot,
                'unit_name_snapshot' => 'Sak', 'qty_ordered' => 10, 'qty_received' => 1,
            ]],
        ])->assertSessionHasErrors('items');
        $this->assertSame(1, GoodsReceipt::count());
        $this->assertEquals(10, $this->stockOf($ctx['material']->id));

        $this->get(route('purchase-transactions.edit', $tx))->assertRedirect();
        $this->put(route('purchase-transactions.update', $tx), $this->txPayload($ctx, 99))->assertSessionHas('error');
        $this->delete(route('purchase-transactions.destroy', $tx))->assertSessionHas('error');
        $this->assertNotNull($tx->fresh());
    }

    public function test_supplier_payment_updates_payables(): void
    {
        $ctx = $this->seedFlow();
        $tx = $ctx['tx'];

        $this->post(route('supplier-payments.store', $tx), ['payment_date' => now()->toDateString(), 'amount' => 700000, 'payment_method' => 'transfer'])
            ->assertSessionHas('error');
        $this->post(route('supplier-payments.store', $tx), ['payment_date' => now()->toDateString(), 'amount' => 150000, 'payment_method' => 'transfer'])
            ->assertSessionMissing('error');

        $tx->refresh();
        $this->assertSame('partial', $tx->payment_status);
        $this->assertEquals(500000, (float) $tx->remaining_amount);
        $this->get('/reports/payables')->assertOk()->assertSee('500.000');
    }

    public function test_purchase_order_to_receipt_to_transaction(): void
    {
        $ctx = $this->seedFlow();
        $m = $ctx['material'];

        $this->post('/purchase-orders', [
            'project_id' => $ctx['project']->id, 'supplier_id' => $ctx['supplier']->id, 'payment_terms' => 'net30',
            'budget_override' => 1, 'budget_override_reason' => 'test',
            'items' => [['material_id' => $m->id, 'material_name' => $m->name, 'unit_name' => 'Sak', 'qty' => 20, 'unit_price' => 60000]],
        ])->assertSessionHasNoErrors();
        $po = PurchaseOrder::firstOrFail();

        $this->post(route('purchase-orders.submit', $po))->assertSessionMissing('error');
        $this->post(route('purchase-orders.approve', $po))->assertSessionMissing('error');
        $this->assertSame('approved', $po->fresh()->status);

        $poItem = $po->items()->first();
        $grPayload = fn ($qty) => [
            'receipt_no' => 'GR-PO-' . $qty, 'receipt_date' => now()->toDateString(), 'received_by' => 'Gudang',
            'items' => [[
                'purchase_order_item_id' => $poItem->id, 'material_name_snapshot' => $m->name,
                'unit_name_snapshot' => 'Sak', 'qty_ordered' => 20, 'qty_received' => $qty,
            ]],
        ];

        $this->post(route('purchase-orders.goods-receipts.store', $po), $grPayload(25))->assertSessionHasErrors('items');
        $this->post(route('purchase-orders.goods-receipts.store', $po), $grPayload(20))->assertSessionHasNoErrors();

        // Stok bertambah per material dari surat jalan PO
        $this->assertEquals(30, $this->stockOf($m->id));
        $po->refresh();
        $this->assertSame('complete', $po->delivery_status);
        $this->assertSame('approved', $po->status, 'PO lengkap diterima tetap bisa dikonversi');

        $this->post(route('purchase-orders.convert', $po))->assertSessionMissing('error');
        $tx = PurchaseTransaction::where('purchase_order_id', $po->id)->firstOrFail();
        $this->assertEquals(1200000, (float) $tx->remaining_amount);
        $this->assertNotSame(PurchaseTransaction::first()->transaction_no, $tx->transaction_no);

        // Surat jalan lagi lewat transaksi tidak boleh menggandakan stok
        $this->post("/purchase-transactions/{$tx->id}/goods-receipts", [
            'receipt_no' => 'SJ-X', 'receipt_date' => now()->toDateString(), 'received_by' => 'Mandor',
            'items' => [[
                'purchase_transaction_item_id' => $tx->items()->first()->id, 'material_name_snapshot' => $m->name,
                'unit_name_snapshot' => 'Sak', 'qty_ordered' => 20, 'qty_received' => 20,
            ]],
        ])->assertSessionHasErrors('items');
        $this->assertEquals(30, $this->stockOf($m->id));

        $this->get(route('purchase-orders.show', $po))->assertOk();
    }

    public function test_purchase_return_reduces_stock_and_is_capped(): void
    {
        $ctx = $this->seedFlow();
        $tx = $ctx['tx'];
        $item = $tx->items->first();
        $payload = fn ($qty) => [
            'purchase_transaction_id' => $tx->id, 'reason' => 'damaged',
            'items' => [[
                'purchase_transaction_item_id' => $item->id, 'material_name_snapshot' => $item->material_name_snapshot,
                'unit_name_snapshot' => 'Sak', 'qty_returned' => $qty, 'unit_price' => 65000,
            ]],
        ];

        $this->post('/purchase-returns', $payload(11))->assertSessionHasErrors('items');
        $this->post('/purchase-returns', $payload(2))->assertSessionHasNoErrors();
        $return = PurchaseReturn::firstOrFail();

        $this->post(route('purchase-returns.submit', $return));
        $this->post(route('purchase-returns.approve', $return));
        $this->post(route('purchase-returns.mark-returned', $return));
        $this->assertSame('returned', $return->fresh()->status);
        $this->assertEquals(8, $this->stockOf($ctx['material']->id));

        $this->post('/purchase-returns', $payload(9))->assertSessionHasErrors('items');
        $this->get(route('purchase-returns.show', $return))->assertOk();

        // Nota kredit mengurangi hutang, tapi tidak dihitung sebagai uang yang dibayar
        $this->post(route('purchase-returns.mark-credited', $return), ['credit_amount' => 999999999])->assertSessionHas('error');
        $this->post(route('purchase-returns.mark-credited', $return), ['credit_note_no' => 'CN-01', 'credit_amount' => 130000])
            ->assertSessionMissing('error');

        $tx->refresh();
        $this->assertEquals(0, (float) $tx->paid_amount);
        $this->assertEquals(520000, (float) $tx->remaining_amount);
        $this->assertSame('partial', $tx->payment_status);
        $this->get('/reports/payables')->assertOk()->assertSee('520.000');
        $this->get(route('purchase-transactions.show', $tx))->assertOk()->assertSee('Nota Kredit Retur');

        // Pelunasan sisa: tidak boleh lebih dari 520.000
        $this->post(route('supplier-payments.store', $tx), ['payment_date' => now()->toDateString(), 'amount' => 650000, 'payment_method' => 'transfer'])
            ->assertSessionHas('error');
        $this->post(route('supplier-payments.store', $tx), ['payment_date' => now()->toDateString(), 'amount' => 520000, 'payment_method' => 'transfer'])
            ->assertSessionMissing('error');
        $this->assertSame('paid', $tx->fresh()->payment_status);
    }

    public function test_invoice_payment_and_delete_guard(): void
    {
        $this->seedFlow();
        $invoice = Invoice::firstOrFail();

        $this->post(route('invoices.record-payment', $invoice), ['payment_amount' => 1000000])->assertSessionMissing('error');
        $invoice->refresh();
        $this->assertSame('partial', $invoice->payment_status);
        $this->assertSame(1, $invoice->payments()->count());

        $this->delete(route('invoices.destroy', $invoice))->assertSessionHas('error');
        $this->assertNotNull($invoice->fresh());
        $this->get('/reports/receivables')->assertOk()->assertSee('4.550.000');
    }

    public function test_stock_out_cannot_exceed_balance(): void
    {
        $ctx = $this->seedFlow();
        $base = ['material_id' => $ctx['material']->id, 'movement_type' => 'out', 'movement_date' => now()->toDateString()];

        $this->post('/stock', $base + ['qty' => 11])->assertSessionHas('error');
        $this->post('/stock', $base + ['qty' => 4])->assertSessionMissing('error');
        $this->assertEquals(6, $this->stockOf($ctx['material']->id));
    }

    public function test_material_request_to_purchase_order(): void
    {
        $ctx = $this->seedFlow();
        $m = $ctx['material'];

        $this->post('/material-requests', [
            'project_id' => $ctx['project']->id, 'urgency' => 'normal',
            'items' => [['material_id' => $m->id, 'material_name' => $m->name, 'unit_name' => 'Sak', 'qty_requested' => 5, 'estimated_price' => 65000]],
        ])->assertSessionHasNoErrors();
        $mr = MaterialRequest::firstOrFail();

        $this->post(route('material-requests.submit', $mr))->assertSessionMissing('error');
        $this->post(route('material-requests.approve', $mr))->assertSessionMissing('error');
        $this->post(route('material-requests.convert-po', $mr))->assertSessionMissing('error');

        $this->assertSame(1, PurchaseOrder::count());
        $this->get(route('material-requests.show', $mr))->assertOk();
    }
}
