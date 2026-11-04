<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxBp21Record;
use App\Models\TaxPph23Record;
use App\Models\TaxPphFinalRecord;
use App\Models\TaxPpnKeluaran;
use App\Models\TaxPpnMasukan;
use App\Models\TaxSetoran;
use App\Models\TaxSetting;
use App\Services\Tax\TaxCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaxDashboardController extends Controller
{
    public function index(): View
    {
        $setting  = TaxSetting::getSetting();
        $periode  = now()->format('Y-m');
        $prevMonth = now()->subMonth()->format('Y-m');

        // ── Ringkasan Periode Ini ──────────────────────────────────────────────
        $ppnKuPeriode  = TaxPpnKeluaran::where('periode', $periode)->sum('ppn');
        $ppnMaPeriode  = TaxPpnMasukan::where('periode', $periode)->where('dapat_dikreditkan', true)->sum('ppn');
        $ppnKurang     = max(0, $ppnKuPeriode - $ppnMaPeriode);
        $ppnLebih      = max(0, $ppnMaPeriode - $ppnKuPeriode);

        $pphFinalPeriode  = TaxPphFinalRecord::where('periode', $periode)->sum('pph_dipotong');
        $pph23Periode     = TaxPph23Record::where('periode', $periode)->sum('pph_dipotong');
        $bp21Periode      = TaxBp21Record::where('masa_pajak', $periode)->sum('pph_dipotong');

        // ── Setoran Jatuh Tempo / Belum Bayar ────────────────────────────────
        $overdueSetoran = TaxSetoran::where('status', 'belum_bayar')
            ->whereDate('jatuh_tempo', '<', today())
            ->orderBy('jatuh_tempo')
            ->get();

        $upcomingSetoran = TaxSetoran::where('status', 'belum_bayar')
            ->whereDate('jatuh_tempo', '>=', today())
            ->whereDate('jatuh_tempo', '<=', today()->addDays(14))
            ->orderBy('jatuh_tempo')
            ->get();

        // ── Statistik 6 Bulan Terakhir ────────────────────────────────────────
        $chartLabels = [];
        $chartPpn    = [];
        $chartPphFin = [];
        $chartPph23  = [];
        $chartBp21   = [];

        for ($i = 5; $i >= 0; $i--) {
            $d   = now()->subMonths($i);
            $key = $d->format('Y-m');
            $chartLabels[] = TaxCalculatorService::formatPeriode($key);

            $chartPpn[]    = (float) TaxPpnKeluaran::where('periode', $key)->sum('ppn');
            $chartPphFin[] = (float) TaxPphFinalRecord::where('periode', $key)->sum('pph_dipotong');
            $chartPph23[]  = (float) TaxPph23Record::where('periode', $key)->sum('pph_dipotong');
            $chartBp21[]   = (float) TaxBp21Record::where('masa_pajak', $key)->sum('pph_dipotong');
        }

        // ── Rekapitulasi Jenis Pajak Tahun Ini ───────────────────────────────
        $tahun = now()->year;

        $rekap = [
            'ppn_keluaran' => (float) TaxPpnKeluaran::whereYear(DB::raw("STR_TO_DATE(CONCAT(periode,'-01'),'%Y-%m-%d')"), $tahun)->sum('ppn'),
            'ppn_masukan'  => (float) TaxPpnMasukan::whereYear(DB::raw("STR_TO_DATE(CONCAT(periode,'-01'),'%Y-%m-%d')"), $tahun)->where('dapat_dikreditkan', true)->sum('ppn'),
            'pph_final'    => (float) TaxPphFinalRecord::whereYear(DB::raw("STR_TO_DATE(CONCAT(periode,'-01'),'%Y-%m-%d')"), $tahun)->sum('pph_dipotong'),
            'pph23'        => (float) TaxPph23Record::whereYear(DB::raw("STR_TO_DATE(CONCAT(periode,'-01'),'%Y-%m-%d')"), $tahun)->sum('pph_dipotong'),
            'bp21'         => (float) TaxBp21Record::whereYear(DB::raw("STR_TO_DATE(CONCAT(masa_pajak,'-01'),'%Y-%m-%d')"), $tahun)->sum('pph_dipotong'),
        ];
        $rekap['ppn_kurang_lebih'] = $rekap['ppn_keluaran'] - $rekap['ppn_masukan'];

        // ── PPh Final belum terima Bukpot ─────────────────────────────────────
        $pphFinalMenungguBukpot = TaxPphFinalRecord::where('status', 'belum_terima_bukpot')->count();

        // ── PPh 23 belum disetor ──────────────────────────────────────────────
        $pph23BelumSetor = TaxPph23Record::where('status', 'belum_disetor')->count();

        // ── BP21 belum disetor ────────────────────────────────────────────────
        $bp21BelumSetor = TaxBp21Record::where('status', 'belum_disetor')->count();

        // ── Total sudah bayar tahun ini ───────────────────────────────────────
        $sudahBayar = (float) TaxSetoran::where('status', 'sudah_bayar')
            ->whereYear(DB::raw("STR_TO_DATE(CONCAT(periode,'-01'),'%Y-%m-%d')"), $tahun)
            ->sum('jumlah');

        return view('tax.dashboard', compact(
            'setting',
            'periode',
            'ppnKuPeriode',
            'ppnMaPeriode',
            'ppnKurang',
            'ppnLebih',
            'pphFinalPeriode',
            'pph23Periode',
            'bp21Periode',
            'overdueSetoran',
            'upcomingSetoran',
            'chartLabels',
            'chartPpn',
            'chartPphFin',
            'chartPph23',
            'chartBp21',
            'rekap',
            'pphFinalMenungguBukpot',
            'pph23BelumSetor',
            'bp21BelumSetor',
            'sudahBayar',
            'tahun',
        ));
    }
}
