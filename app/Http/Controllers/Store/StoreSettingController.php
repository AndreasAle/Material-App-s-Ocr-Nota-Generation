<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Profil toko & aturan dagang yang bisa diubah pemilik tanpa menyentuh file .env. */
class StoreSettingController extends Controller
{
    public function edit(): View
    {
        return view('store.settings', [
            'values' => [
                'name'           => config('store.name'),
                'address'        => config('store.address'),
                'phone'          => config('store.phone'),
                'footer'         => config('store.footer'),
                'default_margin' => config('store.default_margin'),
                'credit_days'    => config('store.credit_days'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:80'],
            'address'        => ['nullable', 'string', 'max:160'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'footer'         => ['nullable', 'string', 'max:120'],
            'default_margin' => ['required', 'numeric', 'min:0', 'max:500'],
            'credit_days'    => ['required', 'integer', 'min:1', 'max:365'],
        ], [
            'name.required' => 'Nama toko wajib diisi.',
        ]);

        AppSetting::saveMany(collect($data)->mapWithKeys(fn ($v, $k) => ["store.{$k}" => $v])->all());

        return redirect()->route('store.settings.edit')->with('success', 'Profil toko disimpan. Struk berikutnya memakai data baru.');
    }
}
