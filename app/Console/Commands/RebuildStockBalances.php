<?php

namespace App\Console\Commands;

use App\Services\Accounting\StockBalanceService;
use Illuminate\Console\Command;

class RebuildStockBalances extends Command
{
    protected $signature   = 'notago:rebuild-stock-balances';
    protected $description = 'Rebuild stock_balances table dari semua stock_movements';

    public function handle(StockBalanceService $service): int
    {
        $this->info('Membangun ulang saldo stok...');

        $count = $service->rebuildAll();

        $this->info("Selesai! {$count} baris saldo stok berhasil diperbarui.");

        return self::SUCCESS;
    }
}
