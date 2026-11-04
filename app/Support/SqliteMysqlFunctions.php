<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Laporan memakai beberapa fungsi khusus MySQL (YEAR, DATE_FORMAT, CONCAT, ...).
 * Saat aplikasi berjalan di SQLite (test, demo, laptop tanpa MySQL) fungsi itu didaftarkan di sini.
 */
class SqliteMysqlFunctions
{
    public static function register(): void
    {
        try {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                return;
            }
            $pdo = DB::connection()->getPdo();
        } catch (\Throwable) {
            return;
        }

        $toTime = static fn ($value) => $value === null ? null : strtotime((string) $value);
        $format = static fn (string $f) => strtr($f, [
            '%Y' => 'Y', '%y' => 'y', '%m' => 'm', '%c' => 'n', '%d' => 'd', '%e' => 'j',
            '%H' => 'H', '%i' => 'i', '%s' => 's', '%M' => 'F', '%b' => 'M',
        ]);

        $pdo->sqliteCreateFunction('YEAR', fn ($v) => ($t = $toTime($v)) ? (int) date('Y', $t) : null, 1);
        $pdo->sqliteCreateFunction('MONTH', fn ($v) => ($t = $toTime($v)) ? (int) date('n', $t) : null, 1);
        $pdo->sqliteCreateFunction('DAY', fn ($v) => ($t = $toTime($v)) ? (int) date('j', $t) : null, 1);
        $pdo->sqliteCreateFunction('DATE_FORMAT', fn ($v, $f) => ($t = $toTime($v)) ? date($format((string) $f), $t) : null, 2);
        $pdo->sqliteCreateFunction('STR_TO_DATE', fn ($v, $f) => ($t = $toTime($v)) ? date('Y-m-d', $t) : null, 2);
        $pdo->sqliteCreateFunction('CONCAT', fn (...$p) => in_array(null, $p, true) ? null : implode('', $p), -1);
        $pdo->sqliteCreateFunction('NOW', fn () => date('Y-m-d H:i:s'), 0);
        $pdo->sqliteCreateFunction('CURDATE', fn () => date('Y-m-d'), 0);
        $pdo->sqliteCreateFunction('DATEDIFF', fn ($a, $b) => ($ta = $toTime($a)) && ($tb = $toTime($b)) ? intdiv($ta - $tb, 86400) : null, 2);
    }
}
