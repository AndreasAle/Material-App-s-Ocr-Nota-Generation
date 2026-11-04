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
        Queue::fake();
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
        $this->postJson('/purchase-scan-batches/start', [
            'receipts' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertOk();

        $batch = PurchaseScanBatch::firstOrFail();
        $this->assertSame(2, $batch->files()->count());
        Queue::assertPushed(ProcessPurchaseScanBatchJob::class);
    }
}
