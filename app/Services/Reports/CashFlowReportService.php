<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PurchaseTransaction;
use App\Models\SupplierPayment;
use App\Models\SubcontractPayment;
use Illuminate\Support\Facades\DB;

class CashFlowReportService
{
    /**
     * Build cash flow report for a given period.
     * Uses actual payment records (invoice_payments + supplier_payments) when available,
     * falls back to estimates from invoices.paid_amount and purchase_transactions.grand_total.
     */
    public function buildMonthly(int $year, int $month): array
    {
        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        // ── Cash IN: actual invoice payments in period ──
        $cashInFromPayments = InvoicePayment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('amount');

        // Fallback: invoices marked paid/partial in period (if no payment records exist)
        $cashInFallback = Invoice::whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('paid_amount');

        $cashIn = $cashInFromPayments > 0 ? (float) $cashInFromPayments : (float) $cashInFallback;

        // ── Cash OUT: supplier payments in period ──
        $cashOutFromPayments = SupplierPayment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('amount');

        // Fallback: purchase transactions in period
        $cashOutFallback = PurchaseTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->notRejected()
            ->sum('grand_total');

        $cashOutPurchase = $cashOutFromPayments > 0 ? (float) $cashOutFromPayments : (float) $cashOutFallback;

        // Subcontractor payments
        $cashOutSubcontract = (float) SubcontractPayment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('amount');

        $cashOut = $cashOutPurchase + $cashOutSubcontract;
        $netFlow = $cashIn - $cashOut;

        return [
            'year'                => $year,
            'month'               => $month,
            'date_from'           => $dateFrom,
            'date_to'             => $dateTo,
            'cash_in'             => $cashIn,
            'cash_out_purchase'   => $cashOutPurchase,
            'cash_out_subcontract'=> $cashOutSubcontract,
            'cash_out'            => $cashOut,
            'net_flow'            => $netFlow,
            'using_actual'        => $cashInFromPayments > 0 || $cashOutFromPayments > 0,
        ];
    }

    /**
     * Build 12-month rolling cash flow for trend chart.
     */
    public function buildYearly(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = $this->buildMonthly($year, $m);
        }
        return $months;
    }

    /**
     * Build detailed cash-in breakdown by project for a period.
     */
    public function getCashInByProject(string $dateFrom, string $dateTo): array
    {
        return InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('projects', 'projects.id', '=', 'invoices.project_id')
            ->whereBetween('invoice_payments.payment_date', [$dateFrom, $dateTo])
            ->selectRaw('projects.name as project_name, SUM(invoice_payments.amount) as total')
            ->groupBy('projects.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    /**
     * Build detailed cash-out breakdown by supplier for a period.
     */
    public function getCashOutBySupplier(string $dateFrom, string $dateTo): array
    {
        return SupplierPayment::join('purchase_transactions', 'purchase_transactions.id', '=', 'supplier_payments.purchase_transaction_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_transactions.supplier_id')
            ->whereBetween('supplier_payments.payment_date', [$dateFrom, $dateTo])
            ->selectRaw('suppliers.name as supplier_name, SUM(supplier_payments.amount) as total')
            ->groupBy('suppliers.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}
