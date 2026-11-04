<?php

namespace App\Jobs;

use App\Models\PurchaseScanBatch;
use App\Services\Purchases\PurchaseScanBatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPurchaseScanBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $batchId;
    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(PurchaseScanBatchService $purchaseScanBatchService): void
    {
        $batch = PurchaseScanBatch::find($this->batchId);

        if (! $batch) {
            return;
        }

        $purchaseScanBatchService->processBatch($batch);
    }
}