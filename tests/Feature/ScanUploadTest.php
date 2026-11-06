<?php

namespace Tests\Feature;

use App\Jobs\ProcessPurchaseScanBatchJob;
use App\Jobs\ProcessPurchaseScanJob;
use App\Models\PurchaseScanBatch;
use App\Models\PurchaseScanDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Upload Scan Nota di instalasi baru (migrasi bersih). Dulu gagal di server karena
 * kolom progress & tabel file batch tidak ada di migration.
 */
class ScanUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['receipt_ai.single_dispatch' => 'queue']);
        Storage::fake('private');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
    }

    public function test_scan_tables_have_every_column_the_models_use(): void
    {
        foreach ([PurchaseScanDraft::class, PurchaseScanBatch::class, \App\Models\PurchaseScanBatchFile::class] as $class) {
            $m = new $class;
            $this->assertTrue(Schema::hasTable($m->getTable()), "tabel {$m->getTable()} belum ada");
            $this->assertSame([], array_values(array_diff($m->getFillable(), Schema::getColumnListing($m->getTable()))), "kolom kurang di {$m->getTable()}");
        }
    }

    public function test_single_note_upload_creates_draft_and_queues_job(): void
    {
        Queue::fake();
        $this->postJson('/purchase-scans/start', ['receipt' => UploadedFile::fake()->image('nota.jpg')])
            ->assertOk()->assertJson(['success' => true]);

        $draft = PurchaseScanDraft::firstOrFail();
        $this->assertSame(5, (int) $draft->progress);
        $this->assertSame('uploaded', $draft->status);
        Storage::disk('private')->assertExists($draft->file_path);
        Queue::assertPushed(ProcessPurchaseScanJob::class);

        // Status "processing" dipakai worker; dulu ditolak oleh kolom enum
        $draft->update(['status' => 'processing', 'progress' => 40]);
        $this->assertSame('processing', $draft->fresh()->status);

        $this->getJson(route('purchase-scans.progress', $draft))->assertOk();
    }

    public function test_multiple_notes_upload_creates_batch_with_files(): void
    {
        Queue::fake();
        $this->postJson('/purchase-scan-batches/start', [
            'receipts' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertOk();

        $batch = PurchaseScanBatch::firstOrFail();
        $this->assertSame(2, $batch->files()->count());
        Queue::assertPushed(ProcessPurchaseScanBatchJob::class);
    }

    public function test_single_note_can_finish_without_a_cron_worker(): void
    {
        config(['receipt_ai.single_dispatch' => 'sync', 'services.ocr.driver' => 'openai']);
        $this->mock(\App\Services\Purchases\Ocr\ReceiptAiPipelineService::class, function ($mock) {
            $mock->shouldReceive('processStoredFile')->once()->andReturn([
                'items' => [['nama_material' => 'Semen', 'qty' => 1, 'harga' => 50000, 'subtotal' => 50000]],
                'grand_total' => 50000,
                'timings_ms' => ['extraction' => 1200, 'matching' => 5],
            ]);
        });

        $response = $this->postJson('/purchase-scans/start', ['receipt' => UploadedFile::fake()->image('nota.jpg')]);
        $response->assertOk()->assertJson(['success' => true, 'result_ready' => true]);
        $draft = PurchaseScanDraft::firstOrFail();
        $this->assertSame('processed', $draft->status);
        $this->assertSame(1200, data_get($draft->parsed_json, 'meta.timings_ms.extraction'));
        $response->assertJsonPath('redirect_url', route('purchase-scans.compare', $draft));
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_immediate_scan_failure_is_not_reported_as_success(): void
    {
        config(['receipt_ai.single_dispatch' => 'sync', 'services.ocr.driver' => 'openai']);
        $this->mock(\App\Services\Purchases\Ocr\ReceiptAiPipelineService::class, function ($mock) {
            $mock->shouldReceive('processStoredFile')->once()->andThrow(new \RuntimeException('API unavailable'));
        });

        $this->postJson('/purchase-scans/start', ['receipt' => UploadedFile::fake()->image('nota.jpg')])
            ->assertStatus(500)->assertJson(['success' => false]);
        $draft = PurchaseScanDraft::firstOrFail();
        $this->assertSame('failed', $draft->status);
        $this->assertSame(0, $draft->result_ready);
        $this->assertDatabaseCount('jobs', 0);
    }

    /**
     * Di produksi config di-cache (php artisan config:cache) sehingga env() di luar folder config
     * selalu kosong — dulu membuat OCR_DRIVER=openai terbaca "tesseract" di server.
     */
    public function test_env_is_not_called_outside_config_files(): void
    {
        $offenders = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path(), \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && preg_match('/(?<![A-Za-z_>:])env\(/', file_get_contents($file->getPathname()))) {
                $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders, 'Pakai config() — env() dilarang di luar config/: ' . implode(', ', $offenders));
    }

    public function test_ocr_driver_comes_from_config(): void
    {
        config(['services.ocr.driver' => 'ocr_space']);
        $this->assertInstanceOf(\App\Services\Purchases\Ocr\OcrSpaceReceiptOcrService::class, app(\App\Services\Purchases\Ocr\ReceiptOcrService::class));
    }
}
