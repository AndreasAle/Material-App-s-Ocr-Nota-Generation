<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'whatsapp_number'  => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
        ], [
            'name.required'        => 'Nama wajib diisi.',
            'whatsapp_number.max'  => 'Nomor WhatsApp maksimal 20 karakter.',
            'whatsapp_number.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        $user->update([
            'name'            => $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function testWhatsapp(Request $request, WhatsAppService $whatsapp): RedirectResponse
    {
        $user = $request->user();

        if (empty($user->whatsapp_number)) {
            return back()->with('error', 'Nomor WhatsApp belum diisi di profil Anda. Isi terlebih dahulu lalu simpan.');
        }

        if (empty(config('services.fonnte.token'))) {
            return back()->with('error', 'FONNTE_TOKEN belum dikonfigurasi di file .env. Isi token Fonnte terlebih dahulu.');
        }

        $message = "✅ *Tes Notifikasi NOTAGO*\n\n"
            . "Halo *{$user->name}*! 👋\n\n"
            . "Notifikasi WhatsApp dari aplikasi NOTAGO berhasil dikonfigurasi.\n\n"
            . "Nomor ini akan menerima:\n"
            . "• Permintaan & hasil approval pembelian\n"
            . "• Alert anggaran RAB (>80% terpakai)\n"
            . "• Notifikasi penting lainnya\n\n"
            . "_Dikirim pada: " . now()->format('d/m/Y H:i:s') . "_";

        $sent = $whatsapp->send($user->whatsapp_number, $message);

        if ($sent) {
            return back()->with('success', 'Pesan tes berhasil dikirim ke ' . $user->whatsapp_number . '. Cek WhatsApp Anda!');
        }

        return back()->with('error', 'Gagal mengirim pesan tes. Pastikan FONNTE_TOKEN valid dan nomor WhatsApp benar.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Password saat ini tidak sesuai.');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}