<?php

namespace App\Console\Commands;

use App\Mail\LowStockMail;
use App\Models\Material;
use App\Models\User;
use App\Services\MaterialRequestService;
use App\Services\Stock\StockMovementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockAlerts extends Command
{
    protected $signature = 'stock:send-low-alerts {--auto-reorder : Buat Material Request otomatis untuk stok rendah}';
    protected $description = 'Kirim email peringatan untuk material yang stoknya di bawah minimum';

    public function __construct(
        protected StockMovementService $stockService,
        protected MaterialRequestService $mrService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Load all materials with a minimum_stock set
        $materials = Material::whereNotNull('minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->where('is_active', true)
            ->with('unit')
            ->get();

        if ($materials->isEmpty()) {
            $this->info('Tidak ada material dengan batas minimum yang dikonfigurasi.');
            return self::SUCCESS;
        }

        $allStocks = $this->stockService->currentStockAll();

        $lowStockItems = [];

        foreach ($materials as $material) {
            $currentStock = $allStocks[$material->id] ?? 0.0;
            if ($currentStock < $material->minimum_stock) {
                $lowStockItems[] = [
                    'material'      => $material,
                    'current_stock' => $currentStock,
                    'minimum_stock' => (float) $material->minimum_stock,
                ];
            }
        }

        if (empty($lowStockItems)) {
            $this->info('Semua stok material berada di atas batas minimum.');
            return self::SUCCESS;
        }

        $admins = User::whereIn('role', ['admin', 'pimpinan'])->whereNotNull('email')->get();

        try {
            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new LowStockMail($lowStockItems));
            }
            $this->info(count($lowStockItems) . ' material stok rendah. Email dikirim ke ' . $admins->count() . ' admin/pimpinan.');
        } catch (\Throwable $e) {
            $this->error("Gagal kirim email low stock: {$e->getMessage()}");
        }

        // Auto-reorder jika flag diberikan
        if ($this->option('auto-reorder')) {
            $created = $this->mrService->autoReorder($lowStockItems);
            $this->info("Auto-reorder: {$created} Material Request dibuat otomatis.");
        }

        return self::SUCCESS;
    }
}
