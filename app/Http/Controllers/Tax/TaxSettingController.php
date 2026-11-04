<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxSettingController extends Controller
{
    public function edit(): View
    {
        $setting = TaxSetting::getSetting();

        return view('tax.settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'is_pkp'                 => 'nullable|boolean',
            'kualifikasi_konstruksi' => 'required|in:kecil,menengah,besar,tidak_bersertifikat',
            'jenis_usaha'            => 'required|in:pelaksana,perencana,pengawas,konsultansi',
            'npwp'                   => 'nullable|string|max:30',
            'nama_wp'                => 'nullable|string|max:255',
            'alamat_wp'              => 'nullable|string|max:500',
            'klu'                    => 'nullable|string|max:20',
            'kpp'                    => 'nullable|string|max:100',
        ]);

        $data['is_pkp'] = $request->boolean('is_pkp');

        $setting = TaxSetting::getSetting();
        $setting->update($data);

        return redirect()
            ->route('tax.settings.edit')
            ->with('success', 'Pengaturan pajak berhasil disimpan.');
    }
}
