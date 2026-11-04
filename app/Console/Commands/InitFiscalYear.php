<?php

namespace App\Console\Commands;

use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitFiscalYear extends Command
{
    protected $signature   = 'notago:init-fiscal-year {year? : Tahun fiskal (default: tahun berjalan)}';
    protected $description = 'Inisialisasi fiscal year dan 12 periode akuntansi';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);

        if (FiscalYear::where('year', $year)->exists()) {
            $this->warn("Fiscal year {$year} sudah ada.");
            return self::SUCCESS;
        }

        DB::transaction(function () use ($year) {
            $fy = FiscalYear::create([
                'year'       => $year,
                'start_date' => "{$year}-01-01",
                'end_date'   => "{$year}-12-31",
                'status'     => 'open',
            ]);

            for ($m = 1; $m <= 12; $m++) {
                $start = "{$year}-" . str_pad($m, 2, '0', STR_PAD_LEFT) . '-01';
                $end   = date('Y-m-t', strtotime($start));

                AccountingPeriod::create([
                    'fiscal_year_id' => $fy->id,
                    'period_month'   => $m,
                    'period_year'    => $year,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'status'         => 'open',
                ]);
            }
        });

        $this->info("Fiscal year {$year} berhasil diinisialisasi dengan 12 periode.");

        return self::SUCCESS;
    }
}
