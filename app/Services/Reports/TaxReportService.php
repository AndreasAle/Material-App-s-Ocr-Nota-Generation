<?php

namespace App\Services\Reports;

use App\Models\TaxPpnMasukan;
use App\Models\TaxPpnKeluaran;
use App\Models\TaxPphFinalRecord;
use App\Models\TaxPph23Record;
use App\Models\Invoice;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\DB;

class TaxReportService
{
    /**
     * Build comprehensive tax report for a period.
     */
    public function build(string $dateFrom, string $dateTo): array
    {
        // PPN Masukan (input tax from purchases)
        $ppnMasukan = $this->getPpnMasukan($dateFrom, $dateTo);

        // PPN Keluaran (output tax from invoices)
        $ppnKeluaran = $this->getPpnKeluaran($dateFrom, $dateTo);

        // PPH Final (jasa konstruksi)
        $pphFinal = $this->getPphFinal($dateFrom, $dateTo);

        // PPH 23
        $pph23 = $this->getPph23($dateFrom, $dateTo);

        // Net PPN position
        $ppnMasukanTotal  = (float) $ppnMasukan->sum('ppn');
        $ppnKeluaranTotal = (float) $ppnKeluaran->sum('ppn');
        $netPpn           = $ppnKeluaranTotal - $ppnMasukanTotal; // positive = kurang bayar

        return [
            'date_from'         => $dateFrom,
            'date_to'           => $dateTo,
            'ppn_masukan'       => $ppnMasukan,
            'ppn_keluaran'      => $ppnKeluaran,
            'pph_final'         => $pphFinal,
            'pph23'             => $pph23,
            'ppn_masukan_total' => $ppnMasukanTotal,
            'ppn_keluaran_total'=> $ppnKeluaranTotal,
            'net_ppn'           => $netPpn,            // >0 = kurang bayar, <0 = lebih bayar
            'pph_final_total'   => (float) $pphFinal->sum('pph_dipotong'),
            'pph23_total'       => (float) $pph23->sum('pph_dipotong'),
        ];
    }

    private function getPpnMasukan(string $from, string $to)
    {
        return TaxPpnMasukan::with(['purchaseTransaction.supplier'])
            ->whereBetween('tanggal_faktur', [$from, $to])
            ->orderBy('tanggal_faktur')
            ->get();
    }

    private function getPpnKeluaran(string $from, string $to)
    {
        return TaxPpnKeluaran::with(['invoice.project'])
            ->whereBetween('tanggal_faktur', [$from, $to])
            ->orderBy('tanggal_faktur')
            ->get();
    }

    private function getPphFinal(string $from, string $to)
    {
        return TaxPphFinalRecord::with(['invoice.project'])
            ->whereBetween('tanggal_bukti_potong', [$from, $to])
            ->orderBy('tanggal_bukti_potong')
            ->get();
    }

    private function getPph23(string $from, string $to)
    {
        return TaxPph23Record::whereBetween('tanggal_bukti_potong', [$from, $to])
            ->orderBy('tanggal_bukti_potong')
            ->get();
    }

    /**
     * Build monthly SPT summary for a year.
     */
    public function buildMonthlySummary(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $from    = sprintf('%04d-%02d-01', $year, $m);
            $to      = date('Y-m-t', strtotime($from));
            $report  = $this->build($from, $to);
            $months[$m] = [
                'month'              => $m,
                'ppn_masukan_total'  => $report['ppn_masukan_total'],
                'ppn_keluaran_total' => $report['ppn_keluaran_total'],
                'net_ppn'            => $report['net_ppn'],
                'pph_final_total'    => $report['pph_final_total'],
                'pph23_total'        => $report['pph23_total'],
            ];
        }
        return $months;
    }
}
