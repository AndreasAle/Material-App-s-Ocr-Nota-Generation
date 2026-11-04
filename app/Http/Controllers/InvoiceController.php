<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
    ) {}

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------
    public function index(Request $request): View
    {
        $search        = $request->input('search');
        $paymentStatus = $request->input('payment_status');
        $projectId     = $request->input('project_id');
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $overdue       = $request->input('overdue');

        $invoices = Invoice::query()
            ->with(['project:id,name,code', 'creator:id,name'])
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('invoice_no', 'like', "%{$search}%")
                   ->orWhere('client_name', 'like', "%{$search}%");
            }))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->when($projectId,     fn ($q) => $q->where('project_id', $projectId))
            ->when($startDate,     fn ($q) => $q->whereDate('invoice_date', '>=', $startDate))
            ->when($endDate,       fn ($q) => $q->whereDate('invoice_date', '<=', $endDate))
            ->when($overdue === '1', fn ($q) => $q
                ->where('payment_status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
            )
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $stats = [
            'total'       => Invoice::count(),
            'unpaid'      => Invoice::where('payment_status', 'unpaid')->count(),
            'partial'     => Invoice::where('payment_status', 'partial')->count(),
            'paid'        => Invoice::where('payment_status', 'paid')->count(),
            'overdue'     => Invoice::where('payment_status', '!=', 'paid')
                                    ->whereNotNull('due_date')
                                    ->whereDate('due_date', '<', today())
                                    ->count(),
            'total_value' => Invoice::sum('grand_total'),
            'total_paid'  => Invoice::sum('paid_amount'),
            'total_outstanding' => Invoice::where('payment_status', '!=', 'paid')
                                          ->selectRaw('SUM(grand_total - paid_amount)')
                                          ->value('SUM(grand_total - paid_amount)') ?? 0,
        ];

        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);

        return view('invoices.index', compact(
            'invoices', 'stats', 'projects',
            'search', 'paymentStatus', 'projectId', 'startDate', 'endDate', 'overdue',
        ));
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------
    public function create(Request $request): View
    {
        $projects  = Project::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $nextNo    = $this->invoiceService->generateInvoiceNo();

        // Bisa pre-fill dari project
        $projectId = $request->input('project_id');
        $project   = $projectId ? Project::find($projectId) : null;

        return view('invoices.create', compact('projects', 'nextNo', 'project'));
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'client_name'        => ['required', 'string', 'max:255'],
            'invoice_date'       => ['required', 'date'],
            'due_date'           => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'project_id'         => ['nullable', 'exists:projects,id'],
            'discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'tax_percent'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.description'=> ['required', 'string', 'max:255'],
            'items.*.qty'        => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'client_name.required'          => 'Nama klien wajib diisi.',
            'invoice_date.required'         => 'Tanggal invoice wajib diisi.',
            'due_date.after_or_equal'       => 'Jatuh tempo harus sama atau setelah tanggal invoice.',
            'items.required'                => 'Minimal 1 item harus diisi.',
            'items.*.description.required'  => 'Deskripsi item wajib diisi.',
            'items.*.qty.required'          => 'Jumlah item wajib diisi.',
            'items.*.unit_price.required'   => 'Harga satuan item wajib diisi.',
        ]);

        $invoice = $this->invoiceService->store($request->all(), auth()->id());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_no} berhasil dibuat.");
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------
    public function show(Invoice $invoice): View
    {
        $invoice->load(['project', 'items', 'creator']);
        return view('invoices.show', compact('invoice'));
    }

    // -------------------------------------------------------------------------
    // recordPayment
    // -------------------------------------------------------------------------
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'payment_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'payment_amount.min'      => 'Jumlah pembayaran harus lebih dari 0.',
        ]);

        $amount = (float) $request->input('payment_amount');
        $remaining = (float) $invoice->grand_total - (float) $invoice->paid_amount;

        if ($amount > $remaining + 0.01) {
            return back()->with('error', "Pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remaining, 0, ',', '.') . ").");
        }

        // Selalu buat record pembayaran agar riwayat, piutang & arus kas konsisten
        app(\App\Services\Accounting\InvoicePaymentService::class)->store($invoice, [
            'payment_date'   => $request->input('payment_date', now()->toDateString()),
            'amount'         => $amount,
            'payment_method' => $request->input('payment_method', 'transfer'),
        ], (int) auth()->id());

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    // -------------------------------------------------------------------------
    // exportPdf
    // -------------------------------------------------------------------------
    public function exportPdf(Invoice $invoice)
    {
        $invoice->load(['project', 'items', 'creator']);

        $pdf = Pdf::loadView('invoices.exports.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $filename = 'invoice-' . $invoice->invoice_no . '.pdf';

        return $pdf->download($filename);
    }

    // -------------------------------------------------------------------------
    // previewPdf
    // -------------------------------------------------------------------------
    public function previewPdf(Invoice $invoice)
    {
        $invoice->load(['project', 'items', 'creator']);

        $pdf = Pdf::loadView('invoices.exports.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('invoice-' . $invoice->invoice_no . '.pdf');
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------
    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->isPaid() || (float) $invoice->paid_amount > 0) {
            return back()->with('error', 'Invoice yang sudah menerima pembayaran tidak bisa dihapus. Hapus pembayarannya terlebih dahulu.');
        }

        $no = $invoice->invoice_no;
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', "Invoice {$no} berhasil dihapus.");
    }
}
