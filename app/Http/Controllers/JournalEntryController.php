<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\Accounting\ChartOfAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(
        protected ChartOfAccountService $coaService,
    ) {}

    public function index(Request $request): View
    {
        $query = JournalEntry::with('creator')
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->get('date_to'));
        }

        $entries = $query->paginate(25)->withQueryString();

        $stats = [
            'total'  => JournalEntry::count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
            'draft'  => JournalEntry::where('status', 'draft')->count(),
        ];

        return view('accounting.journal.index', compact('entries', 'stats'));
    }

    public function create(): View
    {
        $accounts = $this->coaService->getPostableAccounts();
        $projects = \App\Models\Project::where('is_active', true)->orderBy('name')->get();

        return view('accounting.journal.create', compact('accounts', 'projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date'         => 'required|date',
            'description'        => 'required|string|max:500',
            'lines'              => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'      => 'nullable|numeric|min:0',
            'lines.*.credit'     => 'nullable|numeric|min:0',
            'lines.*.description'=> 'nullable|string|max:255',
            'lines.*.project_id' => 'nullable|exists:projects,id',
        ]);

        $totalDebit  = collect($validated['lines'])->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($validated['lines'])->sum(fn ($l) => (float) ($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'Total debit dan kredit harus seimbang.')
                ->withInput();
        }

        $entry = DB::transaction(function () use ($validated, $totalDebit, $totalCredit) {
            $prefix = 'JE-' . now()->format('Ym') . '-';
            $last   = JournalEntry::where('entry_no', 'like', $prefix . '%')->orderByDesc('entry_no')->first();
            $seq    = $last ? ((int) substr($last->entry_no, strlen($prefix))) + 1 : 1;
            $entryNo = $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'entry_no'       => $entryNo,
                'entry_date'     => $validated['entry_date'],
                'description'    => $validated['description'],
                'total_debit'    => $totalDebit,
                'total_credit'   => $totalCredit,
                'status'         => 'draft',
                'currency'       => 'IDR',
                'created_by'     => auth()->id(),
            ]);

            foreach ($validated['lines'] as $i => $line) {
                $account = \App\Models\ChartOfAccount::findOrFail($line['account_id']);
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $account->id,
                    'account_code'     => $account->code,
                    'account_name'     => $account->name,
                    'debit'            => (float) ($line['debit'] ?? 0),
                    'credit'           => (float) ($line['credit'] ?? 0),
                    'description'      => $line['description'] ?? null,
                    'project_id'       => $line['project_id'] ?? null,
                    'line_order'       => $i + 1,
                ]);
            }

            return $entry;
        });

        return redirect()->route('journal.show', $entry)
            ->with('success', "Jurnal {$entry->entry_no} berhasil dibuat.");
    }

    public function show(JournalEntry $journal): View
    {
        $journal->load(['lines.account', 'lines.project', 'creator', 'poster']);
        return view('accounting.journal.show', compact('journal'));
    }

    public function post(JournalEntry $journal): RedirectResponse
    {
        if ($journal->isPosted()) {
            return back()->with('error', 'Jurnal sudah diposting.');
        }

        $journal->update([
            'status'    => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        return redirect()->route('journal.show', $journal)
            ->with('success', "Jurnal {$journal->entry_no} berhasil diposting.");
    }

    public function void(JournalEntry $journal): RedirectResponse
    {
        if (! $journal->isPosted()) {
            return back()->with('error', 'Hanya jurnal yang sudah diposting yang bisa dibatalkan.');
        }

        $journal->update(['status' => 'void']);

        return redirect()->route('journal.show', $journal)
            ->with('success', "Jurnal {$journal->entry_no} berhasil dibatalkan.");
    }
}
