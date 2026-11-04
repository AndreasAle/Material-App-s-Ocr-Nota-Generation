<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseTransaction;
use App\Models\RabPlan;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alur utama: master data â†’ RAB â†’ transaksi (+lampiran) â†’ surat jalan â†’ invoice â†’ dokumen,
 * lalu semua halaman & laporan dibuka dengan data terisi.
 */
class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('private');
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    protected function seedFlow(): array
    {
        $this->actingAs($this->admin);

        $this->post('/material-categories', ['name' => 'Semen', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post('/units', ['name' => 'Sak', 'code' => 'SAK'])->assertSessionHasNoErrors();
        $this->post('/suppliers', ['name' => 'TB Maju', 'city' => 'Bandung', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post('/projects', ['name' => 'Rumah A', 'code' => 'PRJ-A', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post('/materials', [
            'material_category_id' => 1, 'unit_id' => 1, 'code' => 'SMN-01',
            'name' => 'Semen Tiga Roda', 'default_price' => 65000, 'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $material = Material::firstOrFail();
        $project = Project::firstOrFail();
        $supplier = Supplier::firstOrFail();

        $this->post('/rab-plans', [
            'project_id' => $project->id, 'name' => 'RAB Struktur', 'period_start' => now()->startOfMonth()->toDateString(),
            'items' => [[
                'material_id' => $material->id, 'material_name_snapshot' => $material->name,
                'unit_name_snapshot' => 'Sak', 'qty_planned' => 100, 'price_planned' => 65000,
            ]],
        ])->assertSessionHasNoErrors();
        $rab = RabPlan::firstOrFail();
        $this->post("/rab-plans/{$rab->id}/activate")->assertSessionHasNoErrors();

        $this->post('/purchase-transactions', [
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $supplier->id, 'project_id' => $project->id,
            'note_number' => 'NOTA-001', 'status' => 'posted',
            'items' => [[
                'material_id' => $material->id, 'material_name_snapshot' => $material->name,
                'unit_name_snapshot' => 'Sak', 'qty' => 10, 'price' => 65000,
            ]],
            'attachments' => [UploadedFile::fake()->image('nota.jpg')],
        ])->assertSessionHasNoErrors();
        $tx = PurchaseTransaction::with('items')->firstOrFail();

        $this->post("/purchase-transactions/{$tx->id}/goods-receipts", [
            'receipt_no' => 'SJ-001', 'receipt_date' => now()->toDateString(), 'received_by' => 'Mandor',
            'items' => [[
                'purchase_transaction_item_id' => $tx->items->first()->id,
                'material_name_snapshot' => $material->name, 'unit_name_snapshot' => 'Sak',
                'qty_ordered' => 10, 'qty_received' => 10,
            ]],
        ])->assertSessionHasNoErrors();

        $this->post('/invoices', [
            'client_name' => 'PT Klien', 'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(), 'project_id' => $project->id, 'tax_percent' => 11,
            'items' => [['description' => 'Termin 1', 'qty' => 1, 'unit_price' => 5000000]],
        ])->assertSessionHasNoErrors();

        $this->post('/documents', [
            'project_id' => $project->id, 'title' => 'Gambar Kerja', 'category' => 'gambar',
            'file' => UploadedFile::fake()->create('denah.pdf', 200, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        return compact('material', 'project', 'supplier', 'rab', 'tx');
    }

    public function test_purchase_attachment_is_stored_privately_and_served_with_auth(): void
    {
        ['tx' => $tx] = $this->seedFlow();

        $attachment = PurchaseAttachment::firstOrFail();
        $this->assertSame($tx->id, $attachment->purchase_transaction_id);
        $this->assertSame(1, $tx->fresh()->attachment_count);
        Storage::disk('private')->assertExists($attachment->file_path);
        Storage::disk('public')->assertMissing($attachment->file_path);

        $this->get(route('purchase-transactions.attachments.show', $attachment))->assertOk();

        auth()->logout();
        $this->get(route('purchase-transactions.attachments.show', $attachment))->assertRedirect('/login');

        $this->actingAs($this->admin)
            ->delete(route('purchase-transactions.attachments.destroy', $attachment))
            ->assertSessionHasNoErrors();
        Storage::disk('private')->assertMissing($attachment->file_path);
        $this->assertSame(0, $tx->fresh()->attachment_count);
    }

    public function test_project_document_upload_is_private_and_rejects_scripts(): void
    {
        $this->seedFlow();

        $doc = ProjectDocument::firstOrFail();
        Storage::disk('private')->assertExists($doc->file_path);

        $this->get(route('documents.show', $doc))->assertOk()->assertSee('Gambar Kerja');
        $this->get(route('documents.preview', $doc))->assertOk();
        $this->get(route('documents.download', $doc))->assertOk();

        $this->post('/documents', [
            'project_id' => $doc->project_id, 'title' => 'Shell', 'category' => 'general',
            'file' => UploadedFile::fake()->create('shell.php', 1, 'text/x-php'),
        ])->assertSessionHasErrors('file');
        $this->assertSame(1, ProjectDocument::count());

        $this->delete(route('documents.destroy', $doc))->assertRedirect();
        Storage::disk('private')->assertMissing($doc->file_path);
    }

    public function test_reports_reflect_seeded_data(): void
    {
        $this->seedFlow();

        $this->assertSame(1, GoodsReceipt::count());
        $this->assertSame(1, Invoice::count());
        $this->assertEquals(650000, (float) PurchaseTransaction::sum('grand_total'));

        $this->get('/reports/purchases')->assertOk()->assertSee('650.000');
        $this->get('/reports/inventory-valuation')->assertOk()->assertSee('650.000');
        $this->get('/rab-plans/1')->assertOk()->assertSee('650.000');

        // Laporan keuangan menghitung semua transaksi kecuali yang ditolak
        $this->get('/reports/financial/rab-realisasi')->assertOk()->assertSee('650.000');
        $this->get('/reports/financial')->assertOk()->assertSee('650.000');
        PurchaseTransaction::query()->update(['approval_status' => 'rejected']);
        $this->get('/reports/financial/rab-realisasi')->assertOk()->assertDontSee('Rp 650.000');

        $this->assertEquals(10.0, app(\App\Services\Stock\StockMovementService::class)->currentStockAll()[1] ?? 0);
        $this->get('/search?q=Semen')->assertOk()->assertSee('Semen Tiga Roda');
    }

    public function test_every_page_renders_with_data(): void
    {
        $this->seedFlow();

        $bindings = [
            'purchaseTransaction' => 1, 'transaction' => 1, 'goodsReceipt' => 1, 'invoice' => 1,
            'rabPlan' => 1, 'project' => 1, 'supplier' => 1, 'material' => 1, 'document' => 1,
            'attachment' => 1, 'material_category' => 1, 'unit' => 1, 'user' => 1,
        ];
        $skip = ['logout', 'stock.scan-qr'];
        $failures = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true) || in_array($route->getName(), $skip, true)) {
                continue;
            }
            if (! in_array('auth', $route->gatherMiddleware(), true)) {
                continue;
            }

            $params = $route->parameterNames();
            if (array_diff($params, array_keys($bindings))) {
                continue; // butuh entitas yang tidak dibuat di alur ini
            }

            $uri = $route->uri();
            foreach ($params as $name) {
                $uri = str_replace(['{' . $name . '}', '{' . $name . '?}'], (string) $bindings[$name], $uri);
            }

            $response = $this->actingAs($this->admin)->get('/' . ltrim($uri, '/'));

            if ($response->getStatusCode() >= 500) {
                $message = $response->exception?->getMessage() ?? 'HTTP ' . $response->getStatusCode();
                $failures[] = $uri . ' => ' . mb_substr($message, 0, 300);
            }
        }

        $this->assertSame([], $failures, "Halaman error:\n" . implode("\n", $failures));
    }
}
