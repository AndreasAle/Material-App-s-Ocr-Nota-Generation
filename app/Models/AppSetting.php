<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan toko yang bisa diubah dari aplikasi. Key memakai nama config, mis. "store.name".
 */
class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    /** Key yang boleh diubah dari halaman Profil Toko. */
    public const EDITABLE = [
        'store.name', 'store.address', 'store.phone', 'store.footer',
        'store.default_margin', 'store.credit_days',
    ];

    /** Timpa config dengan nilai dari database (dipanggil saat aplikasi boot). */
    public static function applyToConfig(): void
    {
        try {
            $values = Cache::rememberForever('app_settings', function () {
                return Schema::hasTable('app_settings') ? static::pluck('value', 'key')->all() : [];
            });
        } catch (\Throwable) {
            return; // database belum siap (mis. saat install)
        }

        foreach ($values as $key => $value) {
            if (in_array($key, self::EDITABLE, true) && $value !== null && $value !== '') {
                config([$key => $value]);
            }
        }
    }

    public static function saveMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::EDITABLE, true)) {
                static::updateOrCreate(['key' => $key], ['value' => $value]);
                if ($value !== null && $value !== '') {
                    config([$key => $value]); // langsung berlaku tanpa menunggu request berikutnya
                }
            }
        }
        Cache::forget('app_settings');
    }
}
