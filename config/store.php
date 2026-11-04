<?php

return [
    // Mode Toko: sembunyikan modul kontraktor (RAB, proyek, subkon, Coretax, PO/RFQ, akuntansi)
    'mode' => (bool) env('STORE_MODE', false),

    // Identitas toko untuk struk
    'name'    => env('STORE_NAME', env('APP_NAME', 'Toko Material')),
    'address' => env('STORE_ADDRESS', ''),
    'phone'   => env('STORE_PHONE', ''),
    'footer'  => env('STORE_RECEIPT_FOOTER', 'Terima kasih atas kunjungan Anda'),

    // Margin default untuk saran harga jual (persen)
    'default_margin' => (float) env('STORE_DEFAULT_MARGIN', 15),

    // Lama tempo bon pelanggan (hari)
    'credit_days' => (int) env('STORE_CREDIT_DAYS', 30),

    // Barang datang bersama nota: centang "Barang sudah diterima" secara default
    'auto_receive_default' => (bool) env('STORE_AUTO_RECEIVE', true),

    // Pengingat WhatsApp: hutang supplier yang jatuh tempo dalam N hari
    'payable_reminder_days' => (int) env('STORE_PAYABLE_REMINDER_DAYS', 3),

    // Backup database
    'backup_keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),
    'mysqldump_path'   => env('MYSQLDUMP_PATH', 'mysqldump'),
];
