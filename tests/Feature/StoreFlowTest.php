<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\Stock\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Modul Toko Material end-to-end: barang masuk dari nota → stok → kasir → bon → retur → rekap.
 */
class StoreFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Material $semen;
    private Material $besi;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->owner = User::factory()->create(['role' => 'admin', 'is_active' => true, 'whatsapp_number' => '081200000000']);
        $this->supplier = Supplier::create(['name' => 'TB Sumber Rejeki', 'phone' => '081311112222', 'is_active' => true]);

        $cat = MaterialCategory::create(['name' => 'Bahan', 'slug' => 'bahan', 'is_active' => true]);
        $sak = Unit::create(['name' => 'Sak', 'code' => 'SAK']);
        $batang = Unit::create(['name' => 'Batang', 'code' => 'BTG']);

        $this->actingAs($this->owner);

        // Barang dibuat lewat form master (harga jual, grosir, satuan ganda)
        $this->post('/materials', [
            'material_category_id' => $cat->id, 'unit_id' => $sak->id, 'code' => 'SMN', 'name' => 'Semen Gresik',
            'sell_price' => 70000, 'wholesale_price' => 68000, 'wholesale_min_qty' => 50, 'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $this->post('/materials', [
            'material_category_id' => $cat->id, 'unit_id' => $batang->id, 'code' => 'BSI10', 'name' => 'Besi 10mm',
            'sell_price' => 50000, 'is_active' => 1,
            'units' => [['unit_name' => 'Ikat', 'factor' => 10, 'sell_price' => 480000]],
        ])->assertSessionHasNoErrors();

        $this->semen = Material::where('code', 'SMN')->firstOrFail();
        $this->besi = Material::where('code', 'BSI10')->firstOrFail();
    }

    private function stock(Material $m): float
    {
        return (float) (app(StockMovementService::class)->currentStockAll()[$m->id] ?? 0);
    }

    private function buy(array $items, array $extra = [])
    {
        return $this->post('/purchase-transactions', array_merge([
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'status' => 'posted',
            'mark_received' => 1,
            'items' => $items,
        ], $extra));
    }

    private function sell(array $items, array $extra = [])
    {
        return $this->postJson('/toko/kasir', array_merge(['payment_method' => 'cash', 'items' => $items], $extra));
    }

    private function seedStock(): void
    {
        $this->buy([
            ['material_id' => $this->semen->id, 'material_name_snapshot' => 'Semen Gresik', 'unit_name_snapshot' => 'Sak', 'qty' => 100, 'price' => 60000],
            ['material_id' => $this->besi->id, 'material_name_snapshot' => 'Besi 10mm', 'unit_name_snapshot' => 'Ikat', 'qty' => 5, 'price' => 400000],
        ], ['paid_in_full' => 1, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
    }

    // ── A. Barang masuk ──────────────────────────────────────────────────

    public function test_purchase_marked_received_adds_converted_stock_and_is_paid(): void
    {
        $this->seedStock();

        $tx = PurchaseTransaction::firstOrFail();
        $this->assertSame(1, GoodsReceipt::where('purchase_transaction_id', $tx->id)->count());
        $this->assertEquals(100, $this->stock($this->semen));
        $this->assertEquals(50, $this->stock($this->besi), '5 ikat × 10 batang');
        $this->assertSame('paid', $tx->fresh()->payment_status);

        // HPP per batang = 400.000 / 10
        $this->assertEquals(40000, app(StockMovementService::class)->averageCost($this->besi->id));
        $this->assertEquals(40000, (float) $this->besi->fresh()->last_purchase_price);
    }

    public function test_purchase_without_receipt_does_not_add_stock(): void
    {
        $this->buy([['material_id' => $this->semen->id, 'material_name_snapshot' => 'Semen Gresik', 'unit_name_snapshot' => 'Sak', 'qty' => 10, 'price' => 60000]],
            ['mark_received' => 0])->assertSessionHasNoErrors();

        $this->assertEquals(0, $this->stock($this->semen));
        $this->assertSame('unpaid', PurchaseTransaction::first()->payment_status);
    }

    public function test_new_items_from_note_are_created_as_materials(): void
    {
        $this->buy([
            ['material_name_snapshot' => 'Cat Avian 1kg', 'unit_name_snapshot' => 'Kaleng', 'qty' => 12, 'price' => 45000],
            ['material_code' => 'PPA-34', 'material_name_snapshot' => 'Pipa PVC 3/4', 'unit_name_snapshot' => 'Batang', 'qty' => 20, 'price' => 25000],
        ], ['auto_create_missing_materials' => 1])->assertSessionHasNoErrors();

        $cat = Material::where('name', 'Cat Avian 1kg')->firstOrFail();
        $pipa = Material::where('code', 'PPA-34')->firstOrFail();
        $this->assertSame('dari-nota', $cat->category->slug);
        $this->assertSame('Kaleng', $cat->unit->name);
        $this->assertEquals(12, $this->stock($cat));
        $this->assertEquals(20, $this->stock($pipa));

        // Nota berikutnya dengan nama sama tidak membuat barang dobel
        $this->buy([['material_name_snapshot' => 'Cat Avian 1kg', 'unit_name_snapshot' => 'Kaleng', 'qty' => 3, 'price' => 45000]],
            ['auto_create_missing_materials' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, Material::where('name', 'Cat Avian 1kg')->count());
        $this->assertEquals(15, $this->stock($cat));
    }

    public function test_price_increase_alert_and_apply_suggested_price(): void
    {
        $this->seedStock();

        $res = $this->buy([['material_id' => $this->semen->id, 'material_name_snapshot' => 'Semen Gresik', 'unit_name_snapshot' => 'Sak', 'qty' => 10, 'price' => 72000]]);
        $alerts = collect(session('price_alerts'));
        $semenAlert = $alerts->firstWhere('material_id', $this->semen->id);
        $this->assertSame('below_cost', $semenAlert['type'], 'harga beli 72rb ≥ harga jual 70rb');
        $this->assertEquals(82800, $semenAlert['suggested'], '72.000 + 15% dibulatkan ke 100');

        $this->followRedirects($res)->assertSee('Perhatian harga barang');

        $this->post('/toko/harga/pakai-saran', ['material_ids' => [$this->semen->id]])->assertSessionHas('success');
        $this->assertEquals(82800, (float) $this->semen->fresh()->sell_price);
    }

    // ── B. Penjualan ─────────────────────────────────────────────────────

    public function test_cash_sale_with_unit_conversion_and_wholesale(): void
    {
        $this->seedStock();

        $res = $this->sell([
            ['material_id' => $this->semen->id, 'qty' => 2],
            ['material_id' => $this->besi->id, 'unit_name' => 'Batang', 'qty' => 3],
            ['material_id' => $this->besi->id, 'unit_name' => 'Ikat', 'qty' => 1],
        ], ['cash_received' => 800000])->assertOk();

        $sale = Sale::with('items')->firstOrFail();
        $this->assertEquals(140000 + 150000 + 480000, (float) $sale->grand_total);
        $this->assertEquals(30000, (float) $sale->change_amount);
        $this->assertSame('paid', $sale->payment_status);
        $this->assertEquals(98, $this->stock($this->semen));
        $this->assertEquals(50 - 3 - 10, $this->stock($this->besi));
        // HPP: 2×60.000 + 13×40.000
        $this->assertEquals(640000, (float) $sale->cost_total);

        $this->get($res->json('receipt_url'))->assertOk()->assertSee('Semen Gresik')->assertSee('wa.me');

        // Grosir: ≥ 50 sak pakai harga grosir
        $this->sell([['material_id' => $this->semen->id, 'qty' => 50]], ['payment_method' => 'transfer'])->assertOk();
        $this->assertEquals(50 * 68000, (float) Sale::latest('id')->first()->grand_total);
    }

    public function test_sale_rejects_insufficient_stock_and_short_cash(): void
    {
        $this->seedStock();

        $this->sell([['material_id' => $this->semen->id, 'qty' => 101]])->assertStatus(422)->assertJsonValidationErrors('items');
        $this->sell([['material_id' => $this->semen->id, 'qty' => 1]], ['cash_received' => 1000])->assertStatus(422)->assertJsonValidationErrors('cash_received');
        $this->sell([], [])->assertStatus(422);
        $this->assertSame(0, Sale::count());
        $this->assertEquals(100, $this->stock($this->semen));
    }

    public function test_credit_sale_limit_payment_and_return(): void
    {
        $this->seedStock();
        $customer = Customer::create(['name' => 'Pak Mandor', 'phone' => '081299990000', 'credit_limit' => 500000, 'is_active' => true]);

        $this->sell([['material_id' => $this->semen->id, 'qty' => 2]], ['payment_method' => 'bon'])
            ->assertStatus(422)->assertJsonValidationErrors('customer_id');
        $this->sell([['material_id' => $this->semen->id, 'qty' => 9]], ['payment_method' => 'bon', 'customer_id' => $customer->id])
            ->assertStatus(422)->assertJsonValidationErrors('customer_id'); // 630rb > limit

        $this->sell([['material_id' => $this->semen->id, 'qty' => 4]], ['payment_method' => 'bon', 'customer_id' => $customer->id, 'down_payment' => 80000])->assertOk();
        $sale = Sale::firstOrFail();
        $this->assertEquals(200000, (float) $sale->remaining_amount);
        $this->assertSame('partial', $sale->payment_status);
        $this->assertNotNull($sale->due_date);

        // Bayar bon lewat halaman pelanggan (FIFO)
        $this->post("/toko/pelanggan/{$customer->id}/bayar", ['amount' => 999999, 'payment_method' => 'cash'])->assertSessionHasErrors('amount');
        $this->post("/toko/pelanggan/{$customer->id}/bayar", ['amount' => 120000, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
        $this->assertEquals(80000, (float) $sale->fresh()->remaining_amount);

        // Retur 2 sak (140rb): 80rb melunasi bon, 60rb dikembalikan tunai
        $item = $sale->items()->first();
        $this->post("/toko/penjualan/{$sale->id}/retur", ['qty' => [$item->id => 3]])->assertSessionHasNoErrors();
        $sale->refresh();
        $this->assertEquals(0, (float) $sale->remaining_amount);
        $this->assertSame('paid', $sale->payment_status);
        $this->assertEquals(210000, (float) $sale->returned_total);
        $this->assertEquals(130000, (float) $sale->returns()->first()->refund_amount);
        $this->assertEquals(100 - 4 + 3, $this->stock($this->semen));

        // Tidak bisa retur melebihi sisa
        $this->post("/toko/penjualan/{$sale->id}/retur", ['qty' => [$item->id => 2]])->assertSessionHasErrors('items');

        $this->get("/toko/pelanggan/{$customer->id}")->assertOk()->assertSee('Pak Mandor');
        $this->get("/toko/penjualan/{$sale->id}")->assertOk()->assertSee('Riwayat Retur');
    }

    // ── C. Rekap & laporan ───────────────────────────────────────────────

    public function test_recap_top_products_restock_and_reminders(): void
    {
        $this->seedStock();
        $this->semen->update(['minimum_stock' => 95]);
        $customer = Customer::create(['name' => 'Bu Rina', 'phone' => '081277778888', 'is_active' => true]);

        $this->sell([['material_id' => $this->semen->id, 'qty' => 10]], ['cash_received' => 700000])->assertOk();
        $this->sell([['material_id' => $this->besi->id, 'qty' => 2]], ['payment_method' => 'bon', 'customer_id' => $customer->id])->assertOk();

        $this->get('/toko/rekap?period=today')->assertOk()
            ->assertSee('Rp 800.000')          // omzet 700rb + 100rb
            ->assertSee('Rp 120.000');         // laba: (70-60)×10 + (50-40)×2 = 120rb
        $this->get('/toko/rekap/pdf?period=today')->assertOk();
        $this->get('/toko/terlaris?period=month')->assertOk()->assertSee('Semen Gresik');
        $this->get('/toko/saran-kulakan')->assertOk()->assertSee('Semen Gresik')->assertSee('TB Sumber Rejeki');
        $this->get('/toko')->assertOk()->assertSee('Perlu dikerjakan')->assertSee('Stok perlu dibeli lagi');

        // Bon jatuh tempo hari ini → pengingat ke pelanggan & pemilik
        Sale::where('customer_id', $customer->id)->update(['due_date' => now()->toDateString()]);
        $exit = Artisan::call('store:send-reminders', ['--dry-run' => true]);
        $this->assertSame(0, $exit);
        $out = Artisan::output();
        $this->assertStringContainsString('081277778888', $out);
        $this->assertStringContainsString('Bu Rina', $out);
    }

    // ── D. Mode Toko & role Kasir ────────────────────────────────────────

    public function test_cashier_role_is_limited_to_selling(): void
    {
        $this->seedStock();
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $this->actingAs($kasir);

        $this->get('/dashboard')->assertRedirect(route('store.pos'));
        $this->get('/toko/kasir')->assertOk()->assertSee('Kasir')->assertDontSee('Coretax');
        $this->get('/settings')->assertOk();
        $this->get('/api/notifications/dropdown')->assertOk();

        foreach (['/toko', '/toko/rekap', '/purchase-transactions', '/materials', '/reports/payables', '/search?q=a'] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->sell([['material_id' => $this->semen->id, 'qty' => 1]])->assertOk();
        $this->get('/toko/penjualan')->assertOk()->assertSee(Sale::first()->sale_no);
    }

    public function test_store_mode_hides_contractor_modules(): void
    {
        config(['store.mode' => true]);

        $this->get('/dashboard')->assertRedirect(route('store.dashboard'));
        $page = $this->get('/toko')->assertOk();
        $page->assertSee('Beranda')->assertSee('Saran Kulakan')->assertSee('Barang Masuk (Foto Nota)');
        foreach (['Coretax', 'Purchase Order', 'RAB &amp; Anggaran', 'Subkontraktor', 'Jurnal Umum'] as $hidden) {
            $page->assertDontSee($hidden, false);
        }

        config(['store.mode' => false]);
        $this->get('/toko')->assertOk()->assertSee('Purchase Order');
    }

    public function test_user_management_accepts_cashier_role(): void
    {
        $this->post('/users', [
            'name' => 'Kasir Satu', 'email' => 'kasir1@toko.test', 'role' => 'kasir',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertSessionHasNoErrors();

        $this->assertSame('kasir', User::where('email', 'kasir1@toko.test')->value('role'));
    }

    // ── UX: profil toko, panduan awal, tugas harian ─────────────────────

    public function test_store_profile_is_editable_and_used_on_receipt(): void
    {
        $this->seedStock();

        $this->put('/toko/profil', [
            'name' => 'TB Maju Jaya', 'address' => 'Jl. Merdeka 5', 'phone' => '0811222333',
            'footer' => 'Barang yang sudah dibeli tidak dapat ditukar', 'default_margin' => 20, 'credit_days' => 14,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('TB Maju Jaya', config('store.name'));
        $this->assertEquals(20, config('store.default_margin'));

        $res = $this->sell([['material_id' => $this->semen->id, 'qty' => 1]])->assertOk();
        $this->get($res->json('receipt_url'))->assertSee('TB Maju Jaya')->assertSee('Jl. Merdeka 5')->assertSee('tidak dapat ditukar');

        // Bon baru memakai tempo 14 hari
        $c = Customer::create(['name' => 'Pak Udin', 'is_active' => true]);
        $this->sell([['material_id' => $this->semen->id, 'qty' => 1]], ['payment_method' => 'bon', 'customer_id' => $c->id])->assertOk();
        $this->assertSame(now()->addDays(14)->toDateString(), Sale::latest('id')->first()->due_date->toDateString());
    }

    public function test_home_guides_owner_with_setup_steps_and_tasks(): void
    {
        // Barang tanpa harga jual & belum ada stok → muncul di panduan & tugas
        Material::create([
            'material_category_id' => $this->semen->material_category_id, 'unit_id' => $this->semen->unit_id,
            'code' => 'PSR', 'name' => 'Pasir', 'sell_price' => 0, 'is_active' => true,
        ]);

        $page = $this->get('/toko')->assertOk();
        $page->assertSee('Siapkan toko Anda')->assertSee('Masukkan stok awal')
            ->assertSee('Barang belum ada harga jual');

        $this->get('/materials?harga=kosong')->assertOk()->assertSee('Pasir')->assertDontSee('Semen Gresik');

        // Harga beli naik melebihi harga jual → tugas "di bawah harga beli"
        $this->semen->update(['last_purchase_price' => 75000]);
        $this->get('/toko')->assertSee('Harga jual di bawah harga beli');
        $this->get('/materials?harga=rugi')->assertOk()->assertSee('Semen Gresik');
    }

    public function test_flash_messages_are_shown_once(): void
    {
        $this->seedStock();
        $c = Customer::create(['name' => 'Bu Sari', 'is_active' => true]);

        $page = $this->from("/toko/pelanggan/{$c->id}")->followingRedirects()->post("/toko/pelanggan/{$c->id}/bayar", ['amount' => 5000, 'payment_method' => 'cash']);
        $page->assertSee('Ada data yang perlu diperbaiki');

        $page = $this->from('/toko/profil')->followingRedirects()->put('/toko/profil', ['name' => 'Toko A', 'default_margin' => 10, 'credit_days' => 30]);
        $this->assertSame(1, substr_count($page->getContent(), 'Profil toko disimpan'));
    }
}
