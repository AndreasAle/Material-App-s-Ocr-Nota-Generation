<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\PurchaseTransaction;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\RabPlan;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use App\Models\Subcontractor;

class SearchController extends Controller
{
    public function search(Request $request): View
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return view('search.results', ['q' => $q, 'results' => [], 'total' => 0]);
        }

        $like = "%{$q}%";
        $results = [];

        // Hasil hanya ditautkan ke halaman yang boleh dibuka oleh role user
        $role       = $request->user()->role;
        $isAdmin    = $role === 'admin';
        $isManager  = in_array($role, ['admin', 'pimpinan'], true);

        // Transaksi Pembelian
        $txs = PurchaseTransaction::with('supplier')
            ->where(fn($w) => $w->where('transaction_no', 'like', $like)
                ->orWhere('note_number', 'like', $like)
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', $like)))
            ->latest()->limit(8)->get();
        foreach ($txs as $tx) {
            $results[] = [
                'type'     => 'Transaksi',
                'icon'     => '🧾',
                'color'    => 'indigo',
                'title'    => $tx->transaction_no,
                'subtitle' => ($tx->supplier->name ?? '-') . ' · ' . ($tx->transaction_date?->format('d M Y') ?? '-'),
                'url'      => route('purchase-transactions.show', $tx),
            ];
        }

        // Material
        $materials = Material::where(fn($w) => $w->where('name', 'like', $like)
                ->orWhere('code', 'like', $like))
            ->where('is_active', true)->limit(8)->get();
        foreach ($materials as $m) {
            $results[] = [
                'type'     => 'Material',
                'icon'     => '🧱',
                'color'    => 'emerald',
                'title'    => $m->name,
                'subtitle' => $m->code ?? 'Tanpa kode',
                'url'      => $isAdmin ? route('materials.show', $m) : route('stock.show', $m),
            ];
        }

        // Supplier
        $suppliers = Supplier::where(fn($w) => $w->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('city', 'like', $like))
            ->where('is_active', true)->limit(6)->get();
        foreach ($suppliers as $s) {
            $results[] = [
                'type'     => 'Supplier',
                'icon'     => '🏭',
                'color'    => 'violet',
                'title'    => $s->name,
                'subtitle' => trim(($s->city ? $s->city . ' · ' : '') . ($s->phone ?? ''), ' · ') ?: '-',
                'url'      => $isAdmin ? route('suppliers.show', $s) : route('suppliers.price-history', $s),
            ];
        }

        // Proyek
        $projects = Project::where(fn($w) => $w->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('location', 'like', $like))
            ->where('is_active', true)->limit(6)->get();
        foreach ($projects as $p) {
            $results[] = [
                'type'     => 'Proyek',
                'icon'     => '🏗️',
                'color'    => 'amber',
                'title'    => $p->name,
                'subtitle' => ($p->code ? $p->code . ' · ' : '') . ($p->location ?? ''),
                'url'      => $isAdmin ? route('projects.show', $p) : route('documents.index', ['project_id' => $p->id]),
            ];
        }

        // RAB
        $rabs = ! $isManager ? collect() : RabPlan::with('project')
            ->where(fn($w) => $w->where('name', 'like', $like)
                ->orWhereHas('project', fn($p) => $p->where('name', 'like', $like)))
            ->where('is_template', false)->latest()->limit(6)->get();
        foreach ($rabs as $r) {
            $results[] = [
                'type'     => 'RAB',
                'icon'     => '📋',
                'color'    => 'cyan',
                'title'    => $r->name,
                'subtitle' => $r->project->name ?? '-',
                'url'      => route('rab-plans.show', $r),
            ];
        }

        // Invoice
        $invoices = ! $isManager ? collect() : Invoice::where(fn($w) => $w->where('invoice_no', 'like', $like)
                ->orWhere('client_name', 'like', $like))
            ->latest()->limit(6)->get();
        foreach ($invoices as $inv) {
            $results[] = [
                'type'     => 'Invoice',
                'icon'     => '💰',
                'color'    => 'rose',
                'title'    => $inv->invoice_no,
                'subtitle' => $inv->client_name . ' · ' . ($inv->invoice_date?->format('d M Y') ?? '-'),
                'url'      => route('invoices.show', $inv),
            ];
        }

        // Purchase Order
        $pos = PurchaseOrder::with('supplier')
            ->where(fn($w) => $w->where('po_no', 'like', $like)
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', $like)))
            ->latest()->limit(6)->get();
        foreach ($pos as $po) {
            $results[] = [
                'type'     => 'Purchase Order',
                'icon'     => '📦',
                'color'    => 'teal',
                'title'    => $po->po_no,
                'subtitle' => ($po->supplier->name ?? '-') . ' · ' . $po->created_at->format('d M Y'),
                'url'      => route('purchase-orders.show', $po),
            ];
        }

        // Permintaan Material
        $mrs = MaterialRequest::where(fn($w) => $w->where('request_no', 'like', $like)
                ->orWhere('notes', 'like', $like))
            ->latest()->limit(6)->get();
        foreach ($mrs as $mr) {
            $results[] = [
                'type'     => 'Permintaan Material',
                'icon'     => '📝',
                'color'    => 'orange',
                'title'    => $mr->request_no,
                'subtitle' => 'Tgl: ' . $mr->created_at->format('d M Y'),
                'url'      => route('material-requests.show', $mr),
            ];
        }

        // Subkontraktor
        $subs = ! $isManager ? collect() : Subcontractor::where(fn($w) => $w->where('name', 'like', $like)
                ->orWhere('specialization', 'like', $like))
            ->where('is_active', true)->limit(5)->get();
        foreach ($subs as $s) {
            $results[] = [
                'type'     => 'Subkontraktor',
                'icon'     => '👷',
                'color'    => 'slate',
                'title'    => $s->name,
                'subtitle' => $s->specialization ?? '-',
                'url'      => route('subcontractors.show', $s),
            ];
        }

        $total = count($results);
        return view('search.results', compact('q', 'results', 'total'));
    }
}
