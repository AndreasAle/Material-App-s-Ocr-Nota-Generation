<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

/**
 * Chart of Accounts Seeder
 * Sesuai standar akuntansi perusahaan konstruksi Indonesia (PSAK)
 */
class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = $this->getAccounts();

        foreach ($accounts as $acc) {
            ChartOfAccount::updateOrCreate(
                ['code' => $acc['code']],
                $acc
            );
        }

        // Set parent_id untuk sub-akun
        $this->setParents();

        $this->command->info('Chart of Accounts berhasil di-seed: ' . ChartOfAccount::count() . ' akun.');
    }

    private function getAccounts(): array
    {
        return [
            // ═══════════════════════════════════════════════════════
            // 1xxx — ASET
            // ═══════════════════════════════════════════════════════
            ['code' => '1000', 'name' => 'ASET',                            'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true,  'sort_order' => 1],
            ['code' => '1100', 'name' => 'Aset Lancar',                     'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true,  'sort_order' => 10],
            ['code' => '1101', 'name' => 'Kas',                             'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 11],
            ['code' => '1102', 'name' => 'Bank BCA',                        'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 12],
            ['code' => '1103', 'name' => 'Bank Mandiri',                    'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 13],
            ['code' => '1104', 'name' => 'Bank BRI',                        'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 14],
            ['code' => '1201', 'name' => 'Piutang Usaha',                   'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 20],
            ['code' => '1202', 'name' => 'Piutang Retensi',                 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 21],
            ['code' => '1203', 'name' => 'Piutang Lain-lain',               'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 22],
            ['code' => '1301', 'name' => 'PPN Masukan',                     'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 30, 'account_subtype' => 'tax'],
            ['code' => '1302', 'name' => 'PPH Dibayar di Muka',             'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 31, 'account_subtype' => 'tax'],
            ['code' => '1401', 'name' => 'Persediaan Material',             'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 40],
            ['code' => '1501', 'name' => 'Biaya Dibayar di Muka',           'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 50],
            // Aset Tetap
            ['code' => '1600', 'name' => 'Aset Tetap',                     'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true,  'sort_order' => 60],
            ['code' => '1601', 'name' => 'Tanah',                          'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 61],
            ['code' => '1602', 'name' => 'Bangunan',                       'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 62],
            ['code' => '1603', 'name' => 'Kendaraan',                      'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 63],
            ['code' => '1604', 'name' => 'Peralatan Konstruksi',            'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 64],
            ['code' => '1605', 'name' => 'Peralatan Kantor',                'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 65],
            ['code' => '1699', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'account_type' => 'asset', 'normal_balance' => 'credit','level' => 3, 'is_header' => false, 'sort_order' => 69],

            // ═══════════════════════════════════════════════════════
            // 2xxx — KEWAJIBAN
            // ═══════════════════════════════════════════════════════
            ['code' => '2000', 'name' => 'KEWAJIBAN',                       'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'sort_order' => 100],
            ['code' => '2100', 'name' => 'Kewajiban Lancar',                'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'sort_order' => 101],
            ['code' => '2101', 'name' => 'Hutang Usaha',                    'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 102],
            ['code' => '2102', 'name' => 'Hutang Bank Jangka Pendek',       'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 103],
            ['code' => '2103', 'name' => 'Hutang Subkontraktor',            'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 104],
            ['code' => '2201', 'name' => 'PPN Keluaran (Hutang PPN)',       'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 110, 'account_subtype' => 'tax'],
            ['code' => '2202', 'name' => 'Hutang PPH Pasal 23',             'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 111, 'account_subtype' => 'tax'],
            ['code' => '2203', 'name' => 'Hutang PPH Final',                'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 112, 'account_subtype' => 'tax'],
            ['code' => '2204', 'name' => 'Hutang PPH Pasal 21',             'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 113, 'account_subtype' => 'tax'],
            ['code' => '2301', 'name' => 'Biaya YMH Dibayar',               'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 120],
            ['code' => '2302', 'name' => 'Pendapatan Diterima di Muka',     'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 121],
            // Kewajiban Jangka Panjang
            ['code' => '2500', 'name' => 'Kewajiban Jangka Panjang',        'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'sort_order' => 130],
            ['code' => '2501', 'name' => 'Hutang Bank Jangka Panjang',      'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'sort_order' => 131],

            // ═══════════════════════════════════════════════════════
            // 3xxx — EKUITAS
            // ═══════════════════════════════════════════════════════
            ['code' => '3000', 'name' => 'EKUITAS',                         'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'sort_order' => 200],
            ['code' => '3101', 'name' => 'Modal Disetor',                   'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 201],
            ['code' => '3102', 'name' => 'Laba Ditahan',                    'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 202],
            ['code' => '3103', 'name' => 'Laba Berjalan',                   'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 203],

            // ═══════════════════════════════════════════════════════
            // 4xxx — PENDAPATAN
            // ═══════════════════════════════════════════════════════
            ['code' => '4000', 'name' => 'PENDAPATAN',                      'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'sort_order' => 300],
            ['code' => '4101', 'name' => 'Pendapatan Jasa Konstruksi',      'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 301],
            ['code' => '4102', 'name' => 'Pendapatan Konsultansi',          'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 302],
            ['code' => '4103', 'name' => 'Pendapatan Desain',               'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 303],
            ['code' => '4201', 'name' => 'Pendapatan Lain-lain',            'account_type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'sort_order' => 310],

            // ═══════════════════════════════════════════════════════
            // 5xxx — HARGA POKOK PENJUALAN (HPP)
            // ═══════════════════════════════════════════════════════
            ['code' => '5000', 'name' => 'HARGA POKOK PENJUALAN',           'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true,  'sort_order' => 400],
            ['code' => '5101', 'name' => 'Beban Material',                  'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 401],
            ['code' => '5102', 'name' => 'Beban Upah Langsung',             'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 402],
            ['code' => '5103', 'name' => 'Beban Subkontraktor',             'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 403],
            ['code' => '5104', 'name' => 'Beban Sewa Peralatan',            'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 404],
            ['code' => '5105', 'name' => 'Beban Overhead Proyek',           'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 405],

            // ═══════════════════════════════════════════════════════
            // 6xxx — BEBAN OPERASIONAL
            // ═══════════════════════════════════════════════════════
            ['code' => '6000', 'name' => 'BEBAN OPERASIONAL',               'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true,  'sort_order' => 500],
            ['code' => '6101', 'name' => 'Beban Gaji & Tunjangan',         'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 501],
            ['code' => '6102', 'name' => 'Beban BPJS',                     'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 502],
            ['code' => '6103', 'name' => 'Beban Sewa Kantor',              'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 503],
            ['code' => '6104', 'name' => 'Beban Listrik & Air',            'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 504],
            ['code' => '6105', 'name' => 'Beban Telekomunikasi',           'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 505],
            ['code' => '6106', 'name' => 'Beban Administrasi & Umum',      'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 506],
            ['code' => '6107', 'name' => 'Beban Penyusutan Aset Tetap',    'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 507],
            ['code' => '6108', 'name' => 'Beban Asuransi',                 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 508],
            ['code' => '6109', 'name' => 'Beban Perjalanan Dinas',         'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 509],
            ['code' => '6110', 'name' => 'Beban Representasi',             'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 510],
            // Beban Keuangan
            ['code' => '6200', 'name' => 'Beban Keuangan',                 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true,  'sort_order' => 520],
            ['code' => '6201', 'name' => 'Beban Bunga Bank',               'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 521],
            ['code' => '6202', 'name' => 'Beban Administrasi Bank',        'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false, 'sort_order' => 522],
            // Beban Pajak
            ['code' => '6301', 'name' => 'Beban PPH Final',                'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 530, 'account_subtype' => 'tax'],
            ['code' => '6302', 'name' => 'Beban PPH Pasal 23',             'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 531, 'account_subtype' => 'tax'],
            // Lain-lain
            ['code' => '6901', 'name' => 'Beban Lain-lain',                'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false, 'sort_order' => 590],
        ];
    }

    private function setParents(): void
    {
        $parentMap = [
            // Aset
            '1100' => '1000', '1101' => '1100', '1102' => '1100', '1103' => '1100', '1104' => '1100',
            '1201' => '1100', '1202' => '1100', '1203' => '1100',
            '1301' => '1100', '1302' => '1100', '1401' => '1100', '1501' => '1100',
            '1600' => '1000',
            '1601' => '1600', '1602' => '1600', '1603' => '1600', '1604' => '1600', '1605' => '1600', '1699' => '1600',
            // Kewajiban
            '2100' => '2000',
            '2101' => '2100', '2102' => '2100', '2103' => '2100',
            '2201' => '2100', '2202' => '2100', '2203' => '2100', '2204' => '2100',
            '2301' => '2100', '2302' => '2100',
            '2500' => '2000', '2501' => '2500',
            // Ekuitas
            '3101' => '3000', '3102' => '3000', '3103' => '3000',
            // Pendapatan
            '4101' => '4000', '4102' => '4000', '4103' => '4000', '4201' => '4000',
            // HPP
            '5101' => '5000', '5102' => '5000', '5103' => '5000', '5104' => '5000', '5105' => '5000',
            // Beban
            '6101' => '6000', '6102' => '6000', '6103' => '6000', '6104' => '6000', '6105' => '6000',
            '6106' => '6000', '6107' => '6000', '6108' => '6000', '6109' => '6000', '6110' => '6000',
            '6200' => '6000', '6201' => '6200', '6202' => '6200',
            '6301' => '6000', '6302' => '6000', '6901' => '6000',
        ];

        $accountIds = ChartOfAccount::pluck('id', 'code');

        foreach ($parentMap as $childCode => $parentCode) {
            $childId  = $accountIds[$childCode] ?? null;
            $parentId = $accountIds[$parentCode] ?? null;
            if ($childId && $parentId) {
                ChartOfAccount::where('id', $childId)->update(['parent_id' => $parentId]);
            }
        }
    }
}
