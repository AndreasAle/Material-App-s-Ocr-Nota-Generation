<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim pengingat invoice jatuh tempo setiap hari pukul 08:00
Schedule::command('invoices:send-due-reminders')->dailyAt('08:00')->timezone('Asia/Jakarta');

// Cek stok menipis + auto-reorder setiap hari pukul 07:00
Schedule::command('stock:send-low-alerts --auto-reorder')->dailyAt('07:00')->timezone('Asia/Jakarta');

// Toko: pengingat WhatsApp bon pelanggan & hutang supplier (setiap pagi)
Schedule::command('store:send-reminders')->dailyAt('08:30')->timezone('Asia/Jakarta');

// Backup database setiap malam, simpan 14 hari (atur BACKUP_KEEP_DAYS)
Schedule::command('store:backup-db')->dailyAt('23:30')->timezone('Asia/Jakarta');
