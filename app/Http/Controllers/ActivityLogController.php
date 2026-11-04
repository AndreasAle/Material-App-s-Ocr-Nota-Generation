<?php

namespace App\Http\Controllers;

use App\Models\PurchaseScanDraft;
use App\Models\PurchaseTransaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $transactionQuery = PurchaseTransaction::query()
            ->with(['user', 'supplier', 'project']);

        $scanQuery = PurchaseScanDraft::query();

        if (method_exists($user, 'isStaff') && $user->isStaff()) {
            $transactionQuery->where('user_id', $user->id);
            $scanQuery->where('created_by', $user->id);
        }

        $transactions = $transactionQuery
            ->latest('updated_at')
            ->get()
            ->flatMap(function (PurchaseTransaction $transaction) {
                $logs = collect();

                $logs->push([
                    'id' => 'trx-created-' . $transaction->id,
                    'type' => 'transaction_created',
                    'group' => 'transaction',
                    'title' => 'Transaksi pembelian dibuat',
                    'description' => 'Transaksi ' . $transaction->transaction_no . ' berhasil dibuat.',
                    'meta' => [
                        'No. Transaksi' => $transaction->transaction_no,
                        'Supplier' => $transaction->supplier?->name ?? '-',
                        'Proyek' => $transaction->project?->name ?? '-',
                        'Status' => ucfirst($transaction->status ?? '-'),
                        'Grand Total' => 'Rp ' . number_format((float) $transaction->grand_total, 0, ',', '.'),
                    ],
                    'actor' => $transaction->user?->name ?? 'System',
                    'created_at' => $transaction->created_at,
                    'url' => route('purchase-transactions.show', $transaction),
                ]);

                if (
                    $transaction->updated_at &&
                    $transaction->created_at &&
                    $transaction->updated_at->ne($transaction->created_at)
                ) {
                    $logs->push([
                        'id' => 'trx-updated-' . $transaction->id,
                        'type' => 'transaction_updated',
                        'group' => 'transaction',
                        'title' => 'Transaksi pembelian diperbarui',
                        'description' => 'Transaksi ' . $transaction->transaction_no . ' mengalami perubahan data.',
                        'meta' => [
                            'No. Transaksi' => $transaction->transaction_no,
                            'Supplier' => $transaction->supplier?->name ?? '-',
                            'Proyek' => $transaction->project?->name ?? '-',
                            'Status' => ucfirst($transaction->status ?? '-'),
                            'Grand Total' => 'Rp ' . number_format((float) $transaction->grand_total, 0, ',', '.'),
                        ],
                        'actor' => $transaction->user?->name ?? 'System',
                        'created_at' => $transaction->updated_at,
                        'url' => route('purchase-transactions.show', $transaction),
                    ]);
                }

                return $logs;
            });

        $scanDrafts = $scanQuery
            ->latest('updated_at')
            ->get()
            ->flatMap(function (PurchaseScanDraft $draft) {
                $logs = collect();

                $logs->push([
                    'id' => 'scan-created-' . $draft->id,
                    'type' => 'scan_uploaded',
                    'group' => 'scan',
                    'title' => 'Draft scan nota dibuat',
                    'description' => 'File scan nota diunggah ke sistem.',
                    'meta' => [
                        'File' => $draft->file_name ?? '-',
                        'Status' => ucfirst($draft->status ?? '-'),
                        'No. Nota' => $draft->detected_note_number ?? '-',
                    ],
                    'actor' => optional($draft->creator)->name ?? 'User',
                    'created_at' => $draft->created_at,
                    'url' => route('purchase-scans.show', $draft),
                ]);

                if (in_array($draft->status, ['processed', 'reviewed', 'imported', 'failed'], true)) {
                    $titleMap = [
                        'processed' => 'Draft scan berhasil diproses',
                        'reviewed' => 'Draft scan selesai direview',
                        'imported' => 'Draft scan diimport ke transaksi',
                        'failed' => 'Draft scan gagal diproses',
                    ];

                    $descMap = [
                        'processed' => 'Hasil OCR dan parsing nota berhasil dibuat.',
                        'reviewed' => 'Draft scan telah ditinjau sebelum transaksi dibuat.',
                        'imported' => 'Draft scan sudah dimasukkan ke form transaksi.',
                        'failed' => 'Sistem gagal memproses file scan nota.',
                    ];

                    $logs->push([
                        'id' => 'scan-status-' . $draft->id,
                        'type' => 'scan_' . $draft->status,
                        'group' => 'scan',
                        'title' => $titleMap[$draft->status] ?? 'Status draft scan berubah',
                        'description' => $descMap[$draft->status] ?? 'Draft scan mengalami perubahan status.',
                        'meta' => [
                            'File' => $draft->file_name ?? '-',
                            'Status' => ucfirst($draft->status ?? '-'),
                            'Tanggal Nota' => $draft->detected_transaction_date ?? '-',
                            'No. Nota' => $draft->detected_note_number ?? '-',
                        ],
                        'actor' => optional($draft->creator)->name ?? 'User',
                        'created_at' => $draft->updated_at ?? $draft->created_at,
                        'url' => route('purchase-scans.show', $draft),
                    ]);
                }

                return $logs;
            });

        $activities = $transactions
            ->merge($scanDrafts)
            ->sortByDesc('created_at')
            ->values();

        if ($request->filled('group')) {
            $activities = $activities
                ->where('group', $request->string('group')->toString())
                ->values();
        }

        if ($request->filled('keyword')) {
            $keyword = mb_strtolower(trim($request->string('keyword')->toString()));

            $activities = $activities->filter(function (array $activity) use ($keyword) {
                $haystack = mb_strtolower(
                    ($activity['title'] ?? '') . ' ' .
                    ($activity['description'] ?? '') . ' ' .
                    implode(' ', array_values($activity['meta'] ?? [])) . ' ' .
                    ($activity['actor'] ?? '')
                );

                return str_contains($haystack, $keyword);
            })->values();
        }

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $activities->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedActivities = new LengthAwarePaginator(
            $currentItems,
            $activities->count(),
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        $summary = [
            'total' => $activities->count(),
            'transaction' => $activities->where('group', 'transaction')->count(),
            'scan' => $activities->where('group', 'scan')->count(),
            'today' => $activities->filter(function (array $activity) {
                return optional($activity['created_at'])->isToday();
            })->count(),
        ];

        return view('activity-logs.index', [
            'activities' => $paginatedActivities,
            'summary' => $summary,
            'filters' => [
                'group' => $request->string('group')->toString(),
                'keyword' => $request->string('keyword')->toString(),
            ],
        ]);
    }
}