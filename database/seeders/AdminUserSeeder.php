<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@rab.test'],
            [
                'name'             => 'Administrator',
                'password'         => Hash::make('password'),
                'role'             => 'admin',
                'whatsapp_number'  => '0895632271826',
                'is_active'        => true,
            ]
        );

        // ── Pimpinan ───────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'pimpinan@rab.test'],
            [
                'name'             => 'Budi Pimpinan',
                'password'         => Hash::make('password'),
                'role'             => 'pimpinan',
                'whatsapp_number'  => '08111000001',   // ganti nomor asli saat production
                'is_active'        => true,
            ]
        );

        // ── Staff Lapangan 1 ───────────────────────────────────
        User::updateOrCreate(
            ['email' => 'staff@rab.test'],
            [
                'name'             => 'Andi Staff',
                'password'         => Hash::make('password'),
                'role'             => 'staff',
                'whatsapp_number'  => '08111000002',   // ganti nomor asli saat production
                'is_active'        => true,
            ]
        );

        // ── Staff Lapangan 2 ───────────────────────────────────
        User::updateOrCreate(
            ['email' => 'staff2@rab.test'],
            [
                'name'             => 'Rini Staff',
                'password'         => Hash::make('password'),
                'role'             => 'staff',
                'whatsapp_number'  => '08111000003',   // ganti nomor asli saat production
                'is_active'        => true,
            ]
        );

        // ── Tim Purchasing ─────────────────────────────────────
        // Akses: Purchase Order, Material Request, Supplier, Price History
        // TIDAK bisa: Laporan keuangan, Pajak, RAB, Invoice, Approve PO
        User::updateOrCreate(
            ['email' => 'purchasing@rab.test'],
            [
                'name'             => 'Tim Purchasing',
                'password'         => Hash::make('purchasing123'),  // password lebih spesifik
                'role'             => 'purchasing',
                'whatsapp_number'  => '08111000004',   // ganti nomor asli saat production
                'is_active'        => true,
            ]
        );
    }
}