<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseTransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PurchaseScanDraftController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PurchaseScanBatchController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\ScannerReportController;
use App\Http\Controllers\RabPlanController;
use App\Http\Controllers\SupplierPriceHistoryController;
use App\Http\Controllers\PurchaseApprovalController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\VendorScorecardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\ProjectComparisonController;
use App\Http\Controllers\SubcontractorController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\Tax\TaxDashboardController;
use App\Http\Controllers\Tax\TaxSettingController;
use App\Http\Controllers\Tax\TaxPpnController;
use App\Http\Controllers\Tax\TaxPphFinalController;
use App\Http\Controllers\Tax\TaxPph23Controller;
use App\Http\Controllers\Tax\TaxSetoranController;
use App\Http\Controllers\Tax\BpputController;
use App\Http\Controllers\Tax\DokLainPpnController;
use App\Http\Controllers\Tax\TaxBp21Controller;
// ─── New accounting & reporting controllers ────────────────────────────────
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\InventoryValuationController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\AccountingDashboardController;
use App\Http\Controllers\Store\CustomerController;
use App\Http\Controllers\Store\PosController;
use App\Http\Controllers\Store\SaleController;
use App\Http\Controllers\Store\StoreReportController;
use App\Http\Controllers\Store\StoreSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('splash');
})->name('splash');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Semua user yang login (termasuk kasir): pengaturan akun & notifikasi
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');

    // ════════════════════════════════════════════════════════════════════
    //  TOKO — Kasir, penjualan, pelanggan & bon (Pemilik/admin, staff, kasir)
    // ════════════════════════════════════════════════════════════════════
    Route::middleware('role:admin,staff,kasir')->prefix('toko')->name('store.')->group(function () {
        Route::get('/kasir', [PosController::class, 'index'])->name('pos');
        Route::get('/kasir/cari', [PosController::class, 'search'])->name('pos.search');
        Route::post('/kasir', [PosController::class, 'store'])->name('pos.store');

        Route::get('/penjualan', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/penjualan/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/penjualan/{sale}/struk', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::post('/penjualan/{sale}/bayar', [SaleController::class, 'storePayment'])->name('sales.payment');
        Route::post('/penjualan/{sale}/retur', [SaleController::class, 'storeReturn'])->name('sales.return');

        Route::get('/pelanggan', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/pelanggan/baru', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/pelanggan', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/pelanggan/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/pelanggan/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/pelanggan/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/pelanggan/{customer}/bayar', [CustomerController::class, 'pay'])->name('customers.pay');
    });

    // Laporan toko & pengaturan harga — Pemilik (admin) & pimpinan
    Route::middleware('role:admin,pimpinan')->prefix('toko')->name('store.')->group(function () {
        Route::get('/', [StoreReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/rekap', [StoreReportController::class, 'recap'])->name('reports.recap');
        Route::get('/rekap/pdf', [StoreReportController::class, 'recapPdf'])->name('reports.recap.pdf');
        Route::get('/terlaris', [StoreReportController::class, 'topProducts'])->name('reports.top');
        Route::get('/saran-kulakan', [StoreReportController::class, 'restock'])->name('reports.restock');
        Route::post('/harga/pakai-saran', [StoreReportController::class, 'applySuggestedPrices'])->name('prices.apply-suggested');
        Route::delete('/pelanggan/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Profil toko — Pemilik (admin)
    Route::middleware('role:admin')->prefix('toko')->name('store.')->group(function () {
        Route::get('/profil', [StoreSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/profil', [StoreSettingController::class, 'update'])->name('settings.update');
    });

        Route::post('/purchase-scans/start', [PurchaseScanDraftController::class, 'start'])
        ->middleware('role:admin,staff,pimpinan,purchasing')
        ->name('purchase-scans.start');

    Route::get('/purchase-scans/{purchaseScanDraft}/progress', [PurchaseScanDraftController::class, 'progress'])
        ->middleware('role:admin,staff,pimpinan,purchasing')
        ->name('purchase-scans.progress');
    Route::get('/purchase-scans/{purchaseScanDraft}/file', [PurchaseScanDraftController::class, 'file'])
        ->middleware('role:admin,staff,pimpinan,purchasing')
        ->name('purchase-scans.file');

    // ════════════════════════════════════════════════════════════════════
    //  OPERASIONAL — Admin, Staff, Pimpinan, Purchasing
    //  purchasing: hanya PO, MR, Supplier, Price History, Goods Receipt
    // ════════════════════════════════════════════════════════════════════
    Route::middleware('role:admin,staff,pimpinan,purchasing')->group(function () {
        Route::resource('purchase-transactions', PurchaseTransactionController::class);

        Route::get(
            'purchase-transactions/material-lookup/by-code',
            [PurchaseTransactionController::class, 'lookupMaterialByCode']
        )->name('purchase-transactions.lookup-material-by-code');

        /*
        |--------------------------------------------------------------------------
        | Export index / list transaksi
        |--------------------------------------------------------------------------
        */
        Route::get(
            'purchase-transactions/export/excel',
            [PurchaseTransactionController::class, 'exportExcel']
        )->name('purchase-transactions.export-index.excel');

        Route::get(
            'purchase-transactions/export/pdf',
            [PurchaseTransactionController::class, 'exportPdf']
        )->name('purchase-transactions.export-index.pdf');

        /*
        |--------------------------------------------------------------------------
        | Export detail 1 transaksi
        |--------------------------------------------------------------------------
        */
        Route::get(
            'purchase-transactions/{purchaseTransaction}/export/pdf',
            [PurchaseTransactionController::class, 'exportDetailPdf']
        )->name('purchase-transactions.export-detail.pdf');

        Route::get(
            'purchase-transactions/{purchaseTransaction}/export/excel',
            [PurchaseTransactionController::class, 'exportDetailExcel']
        )->name('purchase-transactions.export-detail.excel');

        Route::get('/purchase-scans/create', [PurchaseScanDraftController::class, 'create'])->name('purchase-scans.create');
        Route::post('/purchase-scans', [PurchaseScanDraftController::class, 'store'])->name('purchase-scans.store');
        Route::get('/purchase-scans/{purchaseScanDraft}', [PurchaseScanDraftController::class, 'show'])->name('purchase-scans.show');
        Route::post('/purchase-scans/{purchaseScanDraft}/import', [PurchaseScanDraftController::class, 'import'])->name('purchase-scans.import');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
        Route::post('/settings/test-whatsapp', [SettingsController::class, 'testWhatsapp'])->name('settings.test-whatsapp');

        Route::get(
            'purchase-transactions/attachments/{attachment}',
            [PurchaseTransactionController::class, 'showAttachment']
        )->name('purchase-transactions.attachments.show');

        Route::delete(
            'purchase-transactions/attachments/{attachment}',
            [PurchaseTransactionController::class, 'destroyAttachment']
        )->name('purchase-transactions.attachments.destroy');

        Route::get('/purchase-scans/{purchaseScanDraft}/compare', [PurchaseScanDraftController::class, 'compare'])
            ->name('purchase-scans.compare');

        Route::put('/purchase-scans/{purchaseScanDraft}/compare', [PurchaseScanDraftController::class, 'updateCompare'])
            ->name('purchase-scans.compare.update');

        Route::get('purchase-transactions/{purchaseTransaction}/preview-pdf', [PurchaseTransactionController::class, 'previewDetailPdf'])
             ->name('purchase-transactions.preview-detail.pdf');

        Route::get(
            'purchase-transactions/{purchaseTransaction}/goods-receipts/create',
            [GoodsReceiptController::class, 'create']
        )->name('purchase-transactions.goods-receipts.create');

        Route::post(
            'purchase-transactions/{purchaseTransaction}/goods-receipts',
            [GoodsReceiptController::class, 'store']
        )->name('purchase-transactions.goods-receipts.store');

        Route::get(
            'goods-receipts',
            [GoodsReceiptController::class, 'index']
        )->name('goods-receipts.index');

        Route::get(
            'goods-receipts/{goodsReceipt}',
            [GoodsReceiptController::class, 'show']
        )->name('goods-receipts.show');

        Route::get(
            'goods-receipts/{goodsReceipt}/export/pdf',
            [GoodsReceiptController::class, 'exportPdf']
        )->name('goods-receipts.export.pdf');

        Route::get(
            'goods-receipts/{goodsReceipt}/preview/pdf',
            [GoodsReceiptController::class, 'previewPdf']
        )->name('goods-receipts.preview.pdf');

        Route::get('/purchase-scan-batches/create', [PurchaseScanBatchController::class, 'create'])
            ->name('purchase-scan-batches.create');

        Route::post('/purchase-scan-batches/start', [PurchaseScanBatchController::class, 'start'])
            ->name('purchase-scan-batches.start');

        Route::get('/purchase-scan-batches/{purchaseScanBatch}/progress', [PurchaseScanBatchController::class, 'progress'])
            ->name('purchase-scan-batches.progress');
        Route::get('/purchase-scan-batches/{purchaseScanBatch}/files/{file}', [PurchaseScanBatchController::class, 'file'])
            ->name('purchase-scan-batches.file');

        Route::get('/purchase-scan-batches/{purchaseScanBatch}', [PurchaseScanBatchController::class, 'show'])
            ->name('purchase-scan-batches.show');

        Route::post('/purchase-scan-batches/{purchaseScanBatch}/import', [PurchaseScanBatchController::class, 'import'])
            ->name('purchase-scan-batches.import');

        Route::get('/purchase-scan-batches/{purchaseScanBatch}/compare', [PurchaseScanBatchController::class, 'compare'])
            ->name('purchase-scan-batches.compare');

        Route::put('/purchase-scan-batches/{purchaseScanBatch}/compare', [PurchaseScanBatchController::class, 'updateCompare'])
            ->name('purchase-scan-batches.compare.update');


        Route::get('/reports/scanner', [ScannerReportController::class, 'index'])
            ->name('scanner-reports.index');

        Route::get('/reports/purchases', [PurchaseReportController::class, 'index'])
            ->name('purchase-reports.index');

        // Supplier Price History
        Route::get('suppliers/{supplier}/price-history', [SupplierPriceHistoryController::class, 'show'])
            ->name('suppliers.price-history');

        // Purchase Approval — submit (staff & admin)
        Route::post(
            'purchase-transactions/{purchaseTransaction}/submit-approval',
            [PurchaseApprovalController::class, 'submitForApproval']
        )->name('purchase-transactions.submit-approval');

        // Supplier Payments (record pembayaran ke supplier)
        Route::post(
            'purchase-transactions/{transaction}/supplier-payments',
            [SupplierPaymentController::class, 'store']
        )->name('supplier-payments.store');
        Route::delete(
            'purchase-transactions/{transaction}/supplier-payments/{payment}',
            [SupplierPaymentController::class, 'destroy']
        )->name('supplier-payments.destroy');

        // Stock / Inventory
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/scan-qr', [StockController::class, 'scanQr'])->name('stock.scan-qr');
        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::get('/stock/{material}', [StockController::class, 'show'])->name('stock.show');
        Route::post('/stock/{material}/set-minimum', [StockController::class, 'setMinimum'])->name('stock.set-minimum');


        // Material Requests
        Route::resource('material-requests', MaterialRequestController::class)->except(['edit', 'update']);
        Route::post('material-requests/{materialRequest}/submit', [MaterialRequestController::class, 'submit'])->name('material-requests.submit');
        Route::post('material-requests/{materialRequest}/convert-po', [MaterialRequestController::class, 'convertToPo'])->name('material-requests.convert-po');

        // Warehouses
        Route::resource('warehouses', WarehouseController::class);
        Route::get('warehouses/{warehouse}/qr-label', [WarehouseController::class, 'qrLabel'])->name('warehouses.qr-label');
        Route::get('warehouses/{warehouse}/qr-image', [WarehouseController::class, 'qrImage'])->name('warehouses.qr-image');

        // Purchase Orders
        Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update']);
        Route::post('purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])
            ->name('purchase-orders.submit');
        Route::post('purchase-orders/{purchaseOrder}/convert', [PurchaseOrderController::class, 'convert'])
            ->name('purchase-orders.convert');
        Route::post('purchase-orders/{purchaseOrder}/mark-sent', [PurchaseOrderController::class, 'markSent'])
            ->name('purchase-orders.mark-sent');
        Route::post('purchase-orders/{purchaseOrder}/confirm-supplier', [PurchaseOrderController::class, 'confirmSupplier'])
            ->name('purchase-orders.confirm-supplier');
        Route::get('purchase-orders/{purchaseOrder}/check-budget', [PurchaseOrderController::class, 'checkBudget'])
            ->name('purchase-orders.check-budget');

        // GR from PO (Phase A)
        Route::get(
            'purchase-orders/{purchaseOrder}/goods-receipts/create',
            [GoodsReceiptController::class, 'createFromPo']
        )->name('purchase-orders.goods-receipts.create');
        Route::post(
            'purchase-orders/{purchaseOrder}/goods-receipts',
            [GoodsReceiptController::class, 'storeFromPo']
        )->name('purchase-orders.goods-receipts.store');

        // GR confirm inspection
        Route::post(
            'goods-receipts/{goodsReceipt}/confirm-inspection',
            [GoodsReceiptController::class, 'confirmInspection']
        )->name('goods-receipts.confirm-inspection');

        // RFQ / Request for Quotation
        Route::get('rfq', [RfqController::class, 'index'])->name('rfq.index');
        Route::get('rfq/create', [RfqController::class, 'create'])->name('rfq.create');
        Route::post('rfq', [RfqController::class, 'store'])->name('rfq.store');
        Route::get('rfq/{rfq}', [RfqController::class, 'show'])->name('rfq.show');
        Route::post('rfq/{rfq}/send', [RfqController::class, 'send'])->name('rfq.send');
        Route::get('rfq/{rfq}/compare', [RfqController::class, 'compare'])->name('rfq.compare');
        Route::post('rfq/{rfq}/close', [RfqController::class, 'close'])->name('rfq.close');
        Route::get('rfq/{rfq}/responses/{response}/edit', [RfqController::class, 'editResponse'])->name('rfq.edit-response');
        Route::put('rfq/{rfq}/responses/{response}', [RfqController::class, 'submitResponse'])->name('rfq.submit-response');

        // Purchase Returns (Return to Vendor)
        Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('purchase-transactions/{purchaseTransaction}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::post('purchase-returns/{purchaseReturn}/submit', [PurchaseReturnController::class, 'submit'])->name('purchase-returns.submit');
        Route::post('purchase-returns/{purchaseReturn}/mark-returned', [PurchaseReturnController::class, 'markReturned'])->name('purchase-returns.mark-returned');
        Route::post('purchase-returns/{purchaseReturn}/mark-credited', [PurchaseReturnController::class, 'markCredited'])->name('purchase-returns.mark-credited');

        // Vendor Scorecard
        Route::get('suppliers/{supplier}/scorecard', [VendorScorecardController::class, 'show'])->name('suppliers.scorecard');
        Route::post('suppliers/{supplier}/scorecard/recalculate', [VendorScorecardController::class, 'recalculate'])->name('suppliers.scorecard.recalculate');
        Route::get('suppliers/leaderboard', [VendorScorecardController::class, 'leaderboard'])->name('suppliers.leaderboard');

        // Global Search
        Route::get('/search', [SearchController::class, 'search'])->name('search');

        // Dokumen Proyek
        Route::get('/documents', [ProjectDocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/create', [ProjectDocumentController::class, 'create'])->name('documents.create');
        Route::post('/documents', [ProjectDocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}', [ProjectDocumentController::class, 'show'])->name('documents.show');
        Route::get('/documents/{document}/download', [ProjectDocumentController::class, 'download'])->name('documents.download');
        Route::get('/documents/{document}/preview', [ProjectDocumentController::class, 'preview'])->name('documents.preview');
        Route::delete('/documents/{document}', [ProjectDocumentController::class, 'destroy'])->name('documents.destroy');

    }); // end role:admin,staff,pimpinan

    Route::post('/suppliers/quick-store-from-scan', [SupplierController::class, 'quickStoreFromScan'])
        ->name('suppliers.quick-store-from-scan');

    // ════════════════════════════════════════════════════════════════════
    //  MANAJEMEN & KEUANGAN — Admin & Pimpinan SAJA (Staff lapangan tidak boleh akses)
    // ════════════════════════════════════════════════════════════════════
    Route::middleware('role:admin,pimpinan')->group(function () {

        // Purchase Approval — approve/reject
        Route::post(
            'purchase-transactions/{purchaseTransaction}/approve',
            [PurchaseApprovalController::class, 'approve']
        )->name('purchase-transactions.approve');

        Route::post(
            'purchase-transactions/{purchaseTransaction}/reject',
            [PurchaseApprovalController::class, 'reject']
        )->name('purchase-transactions.reject');

        // PO Approve / Reject / Close
        Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
            ->name('purchase-orders.approve');
        Route::post('purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])
            ->name('purchase-orders.reject');
        Route::post('purchase-orders/{purchaseOrder}/close', [PurchaseOrderController::class, 'close'])
            ->name('purchase-orders.close');

        // RFQ Award (needs pimpinan/admin)
        Route::post('rfq/{rfq}/award', [RfqController::class, 'award'])->name('rfq.award');

        // Purchase Return Approve/Reject
        Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve');
        Route::post('purchase-returns/{purchaseReturn}/reject', [PurchaseReturnController::class, 'reject'])->name('purchase-returns.reject');

        // Vendor Scorecard leaderboard recalc all
        Route::post('suppliers/leaderboard/recalculate-all', [VendorScorecardController::class, 'recalculateAll'])->name('suppliers.leaderboard.recalculate-all');

        // Material Request Approve / Reject
        Route::post('material-requests/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])
            ->name('material-requests.approve');
        Route::post('material-requests/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])
            ->name('material-requests.reject');

        // RAB Planning Module
        Route::resource('rab-plans', RabPlanController::class);
        Route::post('rab-plans/{rabPlan}/activate', [RabPlanController::class, 'activate'])
            ->name('rab-plans.activate');
        Route::post('rab-plans/{rabPlan}/close', [RabPlanController::class, 'close'])
            ->name('rab-plans.close');
        Route::post('rab-plans/{rabPlan}/revise', [RabPlanController::class, 'revise'])
            ->name('rab-plans.revise');
        Route::get('/rab-plans-templates', [RabPlanController::class, 'templates'])->name('rab-plans.templates');
        Route::post('/rab-plans/{rabPlan}/save-as-template', [RabPlanController::class, 'saveAsTemplate'])->name('rab-plans.save-as-template');
        Route::get('/rab-plans/from-template/{template}', [RabPlanController::class, 'createFromTemplate'])->name('rab-plans.from-template');

        // Invoicing
        Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
        Route::post('invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment'])
            ->name('invoices.record-payment');
        Route::get('invoices/{invoice}/export/pdf', [InvoiceController::class, 'exportPdf'])
            ->name('invoices.export-pdf');
        Route::get('invoices/{invoice}/preview/pdf', [InvoiceController::class, 'previewPdf'])
            ->name('invoices.preview-pdf');

        // Import Excel
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/materials', [ImportController::class, 'importMaterials'])->name('import.materials');
        Route::post('/import/rab-items', [ImportController::class, 'importRabItems'])->name('import.rab-items');
        Route::post('/import/transactions', [ImportController::class, 'importTransactions'])->name('import.transactions');
        Route::get('/import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');

        // Activity Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Subkontraktor & SPK
        Route::resource('subcontractors', SubcontractorController::class);
        Route::get('/subcontracts', [SubcontractorController::class, 'spkIndex'])->name('subcontractors.spk.index');
        Route::get('/subcontracts/create', [SubcontractorController::class, 'spkCreate'])->name('subcontractors.spk.create');
        Route::post('/subcontracts', [SubcontractorController::class, 'spkStore'])->name('subcontractors.spk.store');
        Route::get('/subcontracts/{subcontract}', [SubcontractorController::class, 'spkShow'])->name('subcontractors.spk.show');
        Route::get('/subcontracts/{subcontract}/edit', [SubcontractorController::class, 'spkEdit'])->name('subcontractors.spk.edit');
        Route::put('/subcontracts/{subcontract}', [SubcontractorController::class, 'spkUpdate'])->name('subcontractors.spk.update');
        Route::post('/subcontracts/{subcontract}/payments', [SubcontractorController::class, 'paymentStore'])->name('subcontractors.spk.payment-store');
        Route::delete('/subcontract-payments/{payment}', [SubcontractorController::class, 'paymentDestroy'])->name('subcontractors.spk.payment-destroy');

        // Laporan Keuangan
        Route::get('/reports/profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss.index');
        Route::get('/reports/profit-loss/{project}', [ProfitLossController::class, 'show'])->name('profit-loss.show');
        Route::get('/reports/profit-loss/{project}/export/pdf', [ProfitLossController::class, 'exportPdf'])->name('profit-loss.export-pdf');
        Route::get('/reports/profit-loss/export/excel', [ProfitLossController::class, 'exportExcel'])->name('profit-loss.export-excel');

        Route::get('/reports/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow.index');
        Route::get('/reports/cash-flow/export/pdf', [CashFlowController::class, 'exportPdf'])->name('cash-flow.export-pdf');
        Route::get('/reports/cash-flow/export/excel', [CashFlowController::class, 'exportExcel'])->name('cash-flow.export-excel');

        Route::get('/reports/project-comparison', [ProjectComparisonController::class, 'index'])->name('project-comparison.index');
        Route::get('/reports/project-comparison/result', [ProjectComparisonController::class, 'compare'])->name('project-comparison.result');

        Route::get('/reports/executive-summary', [ExecutiveSummaryController::class, 'index'])->name('executive-summary.index');
        Route::get('/reports/executive-summary/export/pdf', [ExecutiveSummaryController::class, 'exportPdf'])->name('executive-summary.export-pdf');

        Route::prefix('reports/financial')->name('financial-reports.')->group(function () {
            Route::get('/', [FinancialReportController::class, 'index'])->name('index');
            Route::get('/rab-realisasi', [FinancialReportController::class, 'rabRealisasi'])->name('rab-realisasi');
            Route::get('/pengeluaran', [FinancialReportController::class, 'pengeluaran'])->name('pengeluaran');
            Route::get('/surat-jalan', [FinancialReportController::class, 'suratJalan'])->name('surat-jalan');
            Route::get('/pengeluaran/export/pdf', [FinancialReportController::class, 'exportPengeluaranPdf'])->name('pengeluaran.export.pdf');
        });

        // ─────────────────────────────────────────────────────────────────────
        //  INVOICE PAYMENTS (record pembayaran masuk dari klien)
        // ─────────────────────────────────────────────────────────────────────
        Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])
            ->name('invoice-payments.store');
        Route::delete('invoices/{invoice}/payments/{payment}', [InvoicePaymentController::class, 'destroy'])
            ->name('invoice-payments.destroy');

        // ─────────────────────────────────────────────────────────────────────
        //  PIUTANG & HUTANG
        // ─────────────────────────────────────────────────────────────────────
        Route::get('/reports/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
        Route::get('/reports/receivables/export/excel', [ReceivableController::class, 'exportExcel'])->name('receivables.export-excel');

        Route::get('/reports/payables', [PayableController::class, 'index'])->name('payables.index');
        Route::get('/reports/payables/export/excel', [PayableController::class, 'exportExcel'])->name('payables.export-excel');

        // ─────────────────────────────────────────────────────────────────────
        //  REKAP PAJAK
        // ─────────────────────────────────────────────────────────────────────
        Route::get('/reports/tax-summary', [TaxReportController::class, 'index'])->name('tax-report.index');
        Route::get('/reports/tax-summary/export/excel', [TaxReportController::class, 'exportExcel'])->name('tax-report.export-excel');

        // ─────────────────────────────────────────────────────────────────────
        //  NILAI STOK / INVENTORY VALUATION
        // ─────────────────────────────────────────────────────────────────────
        Route::get('/reports/inventory-valuation', [InventoryValuationController::class, 'index'])->name('inventory-valuation.index');
        Route::get('/reports/inventory-valuation/export/excel', [InventoryValuationController::class, 'exportExcel'])->name('inventory-valuation.export-excel');

        // ─────────────────────────────────────────────────────────────────────
        //  ACCOUNTING MODULE — COA, Jurnal, Neraca Saldo, Dashboard
        // ─────────────────────────────────────────────────────────────────────
        Route::get('/accounting', [AccountingDashboardController::class, 'index'])->name('accounting.dashboard');

        Route::get('/accounting/coa', [ChartOfAccountController::class, 'index'])->name('coa.index');
        Route::get('/accounting/coa/create', [ChartOfAccountController::class, 'create'])->name('coa.create');
        Route::post('/accounting/coa', [ChartOfAccountController::class, 'store'])->name('coa.store');
        Route::get('/accounting/coa/{coa}/edit', [ChartOfAccountController::class, 'edit'])->name('coa.edit');
        Route::put('/accounting/coa/{coa}', [ChartOfAccountController::class, 'update'])->name('coa.update');
        Route::delete('/accounting/coa/{coa}', [ChartOfAccountController::class, 'destroy'])->name('coa.destroy');

        Route::get('/accounting/journal', [JournalEntryController::class, 'index'])->name('journal.index');
        Route::get('/accounting/journal/create', [JournalEntryController::class, 'create'])->name('journal.create');
        Route::post('/accounting/journal', [JournalEntryController::class, 'store'])->name('journal.store');
        Route::get('/accounting/journal/{journal}', [JournalEntryController::class, 'show'])->name('journal.show');
        Route::post('/accounting/journal/{journal}/post', [JournalEntryController::class, 'post'])->name('journal.post');
        Route::post('/accounting/journal/{journal}/void', [JournalEntryController::class, 'void'])->name('journal.void');

        Route::get('/accounting/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance.index');
        Route::get('/accounting/trial-balance/export/excel', [TrialBalanceController::class, 'exportExcel'])->name('trial-balance.export-excel');

        // ─────────────────────────────────────────────────────────────────────
        //  MODUL PAJAK (Coretax) — Admin & Pimpinan saja
        // ─────────────────────────────────────────────────────────────────────
        Route::prefix('tax')->name('tax.')->group(function () {

            Route::get('/',         [TaxDashboardController::class, 'index'])->name('dashboard');

            Route::get('/settings',  [TaxSettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings',  [TaxSettingController::class, 'update'])->name('settings.update');

            Route::prefix('ppn')->name('ppn.')->group(function () {
                Route::get('/',               [TaxPpnController::class, 'index'])->name('index');
                Route::get('/masukan/create',         [TaxPpnController::class, 'createMasukan'])->name('masukan.create');
                Route::post('/masukan',               [TaxPpnController::class, 'storeMasukan'])->name('masukan.store');
                Route::get('/masukan/{masukan}/edit', [TaxPpnController::class, 'editMasukan'])->name('masukan.edit');
                Route::put('/masukan/{masukan}',      [TaxPpnController::class, 'updateMasukan'])->name('masukan.update');
                Route::delete('/masukan/{masukan}',   [TaxPpnController::class, 'destroyMasukan'])->name('masukan.destroy');
                Route::get('/keluaran/create',[TaxPpnController::class, 'createKeluaran'])->name('keluaran.create');
                Route::post('/keluaran',      [TaxPpnController::class, 'storeKeluaran'])->name('keluaran.store');
                Route::delete('/keluaran/{keluaran}', [TaxPpnController::class, 'destroyKeluaran'])->name('keluaran.destroy');
            });

            Route::prefix('pph-final')->name('pph-final.')->group(function () {
                Route::get('/',                     [TaxPphFinalController::class, 'index'])->name('index');
                Route::get('/create',               [TaxPphFinalController::class, 'create'])->name('create');
                Route::post('/',                    [TaxPphFinalController::class, 'store'])->name('store');
                Route::get('/{record}',             [TaxPphFinalController::class, 'show'])->name('show');
                Route::post('/{record}/konfirm',    [TaxPphFinalController::class, 'konfirmBukpot'])->name('konfirm');
                Route::delete('/{record}',          [TaxPphFinalController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('pph23')->name('pph23.')->group(function () {
                Route::get('/',                     [TaxPph23Controller::class, 'index'])->name('index');
                Route::get('/create',               [TaxPph23Controller::class, 'create'])->name('create');
                Route::post('/',                    [TaxPph23Controller::class, 'store'])->name('store');
                Route::post('/{record}/konfirm',    [TaxPph23Controller::class, 'konfirmSetor'])->name('konfirm');
                Route::delete('/{record}',          [TaxPph23Controller::class, 'destroy'])->name('destroy');
            });

            Route::prefix('setoran')->name('setoran.')->group(function () {
                Route::get('/',                      [TaxSetoranController::class, 'index'])->name('index');
                Route::get('/create',                [TaxSetoranController::class, 'create'])->name('create');
                Route::post('/',                     [TaxSetoranController::class, 'store'])->name('store');
                Route::post('/{setoran}/konfirm',    [TaxSetoranController::class, 'konfirmBayar'])->name('konfirm');
                Route::delete('/{setoran}',          [TaxSetoranController::class, 'destroy'])->name('destroy');
            });

            // ─── Phase 1: BPPU (Bukti Pemotongan PPh Unifikasi) ──────────────
            Route::prefix('bppu')->name('bppu.')->group(function () {
                Route::get('/',                          [BpputController::class, 'index'])->name('index');
                Route::get('/import',                    [BpputController::class, 'importForm'])->name('import');
                Route::post('/import',                   [BpputController::class, 'importProcess'])->name('import.process');
                Route::get('/import/{batch}/preview',    [BpputController::class, 'importPreview'])->name('preview');
                Route::post('/import/{batch}/commit',    [BpputController::class, 'importCommit'])->name('commit');
                Route::get('/export',                    [BpputController::class, 'exportForm'])->name('export');
                Route::post('/export/download',          [BpputController::class, 'exportDownload'])->name('export.download');
                Route::get('/batch/{batch}',             [BpputController::class, 'showBatch'])->name('batch');
            });

            // ─── Phase 2: Dokumen Lain PPN ───────────────────────────────────
            Route::prefix('dok-lain-ppn')->name('dok-lain.')->group(function () {
                Route::get('/',                              [DokLainPpnController::class, 'index'])->name('index');
                Route::get('/import-masukan',               [DokLainPpnController::class, 'importMasukanForm'])->name('import.masukan');
                Route::post('/import-masukan',              [DokLainPpnController::class, 'importMasukanProcess'])->name('import.masukan.process');
                Route::get('/import-keluaran',              [DokLainPpnController::class, 'importKeluaranForm'])->name('import.keluaran');
                Route::post('/import-keluaran',             [DokLainPpnController::class, 'importKeluaranProcess'])->name('import.keluaran.process');
                Route::get('/preview/{batch}',              [DokLainPpnController::class, 'preview'])->name('preview');
                Route::post('/commit/{batch}',              [DokLainPpnController::class, 'commit'])->name('commit');
                Route::get('/export-masukan',               [DokLainPpnController::class, 'exportMasukan'])->name('export.masukan');
                Route::get('/export-keluaran',              [DokLainPpnController::class, 'exportKeluaran'])->name('export.keluaran');
            });

            // ─── Phase 3: BP21 (Bukti Pemotongan PPh Pasal 21) ───────────────
            Route::prefix('bp21')->name('bp21.')->group(function () {
                Route::get('/',                          [TaxBp21Controller::class, 'index'])->name('index');
                Route::get('/create',                    [TaxBp21Controller::class, 'create'])->name('create');
                Route::post('/',                         [TaxBp21Controller::class, 'store'])->name('store');
                Route::get('/import',                    [TaxBp21Controller::class, 'importForm'])->name('import');
                Route::post('/import',                   [TaxBp21Controller::class, 'importProcess'])->name('import.process');
                Route::get('/import/{batch}/preview',    [TaxBp21Controller::class, 'importPreview'])->name('preview');
                Route::post('/import/{batch}/commit',    [TaxBp21Controller::class, 'importCommit'])->name('commit');
                Route::get('/export',                    [TaxBp21Controller::class, 'exportForm'])->name('export');
                Route::post('/export/download',          [TaxBp21Controller::class, 'exportDownload'])->name('export.download');
                Route::get('/{record}',                  [TaxBp21Controller::class, 'show'])->name('show');
                Route::get('/{record}/edit',             [TaxBp21Controller::class, 'edit'])->name('edit');
                Route::put('/{record}',                  [TaxBp21Controller::class, 'update'])->name('update');
                Route::post('/{record}/konfirm',         [TaxBp21Controller::class, 'konfirmSetor'])->name('konfirm');
                Route::delete('/{record}',               [TaxBp21Controller::class, 'destroy'])->name('destroy');
            });
        });

    }); // end role:admin,pimpinan

    Route::middleware('role:admin')->group(function () {
        Route::resources([
            'material-categories' => MaterialCategoryController::class,
            'units' => UnitController::class,
            'suppliers' => SupplierController::class,
            'projects' => ProjectController::class,
            'materials' => MaterialController::class,
        ]);

        // User Management
        Route::resource('users', UserManagementController::class);
        Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::post('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
            ->name('users.toggle-active');
    });
});

require __DIR__ . '/auth.php';
