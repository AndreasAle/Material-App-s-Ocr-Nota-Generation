<?php

namespace App\Services\Store;

use App\Models\Material;
use App\Models\PurchaseScanBatch;
use App\Models\PurchaseScanDraft;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;

/**
 * Menyusun "apa yang perlu dikerjakan" untuk pemilik/admin toko:
 * - langkah persiapan awal (sekali saja), dan
 * - tugas harian yang muncul dari data (nota menunggu dicek, harga kosong, stok menipis, bon telat, dst).
 */
class StoreGuideService
{
    public function __construct(
        protected StoreReportService $reports,
    ) {}

    /**
     * @return array<int, array{key:string, title:string, desc:string, done:bool, url:string, action:string}>
     */
    public function setupSteps(User $user): array
    {
        $materialCount = Material::where('is_active', true)->count();
        $withoutPrice = Material::where('is_active', true)->where(fn ($q) => $q->whereNull('sell_price')->orWhere('sell_price', '<=', 0))->count();

        return [
            [
                'key' => 'profile', 'title' => 'Isi profil toko',
                'desc' => 'Nama, alamat & no. telepon toko tampil di struk.',
                'done' => config('store.name') !== 'Toko Material' && filled(config('store.address')),
                'url' => route('store.settings.edit'), 'action' => 'Isi profil',
            ],
            [
                'key' => 'materials', 'title' => 'Daftarkan barang & harga jual',
                'desc' => $materialCount === 0 ? 'Belum ada barang. Tambah manual atau langsung foto nota supplier.' : ($withoutPrice > 0 ? "{$withoutPrice} barang belum punya harga jual." : 'Semua barang sudah punya harga jual.'),
                'done' => $materialCount > 0 && $withoutPrice === 0,
                'url' => $materialCount === 0 ? route('materials.create') : route('materials.index', ['harga' => 'kosong']), 'action' => $materialCount === 0 ? 'Tambah barang' : 'Isi harga',
            ],
            [
                'key' => 'stock', 'title' => 'Masukkan stok awal',
                'desc' => 'Catat jumlah barang yang ada di gudang sekarang (beserta harga belinya).',
                'done' => StockMovement::exists(),
                'url' => route('stock.create'), 'action' => 'Input stok',
            ],
            [
                'key' => 'minimum', 'title' => 'Atur stok minimum',
                'desc' => 'Supaya aplikasi mengingatkan barang yang hampir habis.',
                'done' => Material::where('minimum_stock', '>', 0)->exists(),
                'url' => route('stock.index'), 'action' => 'Atur',
            ],
            [
                'key' => 'whatsapp', 'title' => 'Isi nomor WhatsApp Anda',
                'desc' => 'Untuk menerima pengingat hutang supplier & bon pelanggan.',
                'done' => filled($user->whatsapp_number),
                'url' => route('settings.index'), 'action' => 'Isi nomor',
            ],
            [
                'key' => 'cashier', 'title' => 'Buat akun kasir (opsional)',
                'desc' => 'Karyawan bisa berjualan tanpa melihat laporan & harga beli.',
                'done' => User::where('role', 'kasir')->exists(),
                'url' => route('users.create'), 'action' => 'Buat akun',
            ],
        ];
    }

    /**
     * Tugas yang perlu ditindaklanjuti hari ini, urut dari yang paling penting.
     *
     * @return array<int, array{tone:string, icon:string, title:string, desc:string, count:int|string, url:string}>
     */
    public function tasks(): array
    {
        $tasks = [];
        $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        // 1. Nota supplier sudah dibaca, tinggal dicek & disimpan
        $drafts = PurchaseScanDraft::where('status', 'processed')->latest()->get(['id']);
        $batches = PurchaseScanBatch::where('status', 'processed')->latest()->get(['id']);
        $pendingNotes = $drafts->count() + $batches->count();
        if ($pendingNotes > 0) {
            $tasks[] = [
                'tone' => 'brand', 'icon' => 'camera',
                'title' => 'Nota supplier menunggu dicek',
                'desc' => 'Hasil foto nota sudah dibaca. Cek angkanya lalu simpan supaya stok bertambah.',
                'count' => $pendingNotes,
                'url' => $drafts->isNotEmpty() ? route('purchase-scans.show', $drafts->first()->id) : route('purchase-scan-batches.show', $batches->first()->id),
            ];
        }

        // 2. Harga jual di bawah harga beli → rugi kalau dijual
        $belowCost = Material::where('is_active', true)->where('sell_price', '>', 0)
            ->whereNotNull('last_purchase_price')->whereColumn('last_purchase_price', '>=', 'sell_price')->count();
        if ($belowCost > 0) {
            $tasks[] = [
                'tone' => 'danger', 'icon' => 'alert',
                'title' => 'Harga jual di bawah harga beli',
                'desc' => 'Harga beli terakhir sudah naik. Perbarui harga jual agar tidak rugi.',
                'count' => $belowCost, 'url' => route('materials.index', ['harga' => 'rugi']),
            ];
        }

        // 3. Barang belum punya harga jual (tidak bisa dijual di kasir)
        $noPrice = Material::where('is_active', true)->where(fn ($q) => $q->whereNull('sell_price')->orWhere('sell_price', '<=', 0))->count();
        if ($noPrice > 0) {
            $tasks[] = [
                'tone' => 'warning', 'icon' => 'tag',
                'title' => 'Barang belum ada harga jual',
                'desc' => 'Barang ini belum bisa dijual di kasir sampai harganya diisi.',
                'count' => $noPrice, 'url' => route('materials.index', ['harga' => 'kosong']),
            ];
        }

        // 4. Bon pelanggan lewat jatuh tempo
        $overdue = Sale::where('remaining_amount', '>', 0)->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString());
        $overdueCount = (clone $overdue)->count();
        if ($overdueCount > 0) {
            $tasks[] = [
                'tone' => 'danger', 'icon' => 'users',
                'title' => 'Bon pelanggan sudah telat',
                'desc' => 'Total ' . $rp((clone $overdue)->sum('remaining_amount')) . '. Tagih lewat tombol WhatsApp di halaman pelanggan.',
                'count' => $overdueCount, 'url' => route('store.customers.index', ['filter' => 'bon']),
            ];
        }

        // 5. Hutang ke supplier jatuh tempo dalam 7 hari
        $payables = PurchaseTransaction::notRejected()->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')->whereDate('due_date', '<=', now()->addDays(7)->toDateString());
        $payableCount = (clone $payables)->count();
        if ($payableCount > 0) {
            $tasks[] = [
                'tone' => 'warning', 'icon' => 'cash',
                'title' => 'Hutang supplier segera jatuh tempo',
                'desc' => 'Total ' . $rp((clone $payables)->sum('remaining_amount')) . ' dalam 7 hari ke depan.',
                'count' => $payableCount, 'url' => route('payables.index'),
            ];
        }

        // 6. Stok habis / di bawah minimum
        $restock = $this->reports->restockSuggestions();
        if ($restock->isNotEmpty()) {
            $empty = $restock->filter(fn ($r) => $r['stock'] <= 0)->count();
            $tasks[] = [
                'tone' => $empty > 0 ? 'danger' : 'warning', 'icon' => 'box',
                'title' => 'Stok perlu dibeli lagi',
                'desc' => ($empty > 0 ? "{$empty} barang sudah habis. " : '') . 'Lihat supplier termurah & pesan lewat WhatsApp.',
                'count' => $restock->count(), 'url' => route('store.reports.restock'),
            ];
        }

        // 7. Pembelian yang barangnya belum dicatat diterima (stok belum bertambah)
        $notReceived = PurchaseTransaction::notRejected()
            ->whereDate('transaction_date', '>=', now()->subDays(30)->toDateString())
            ->whereDoesntHave('goodsReceipts')
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('purchase_transactions', 'purchase_order_id'),
                fn ($q) => $q->whereNull('purchase_order_id'))
            ->count();
        if ($notReceived > 0) {
            $tasks[] = [
                'tone' => 'info', 'icon' => 'truck',
                'title' => 'Barang belanjaan belum diterima',
                'desc' => 'Pembelian sudah dicatat tapi barangnya belum ditandai masuk gudang.',
                'count' => $notReceived, 'url' => route('purchase-transactions.index'),
            ];
        }

        return $tasks;
    }
}
