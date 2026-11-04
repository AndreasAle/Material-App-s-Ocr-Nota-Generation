<?php

namespace App\Services\Purchases\Ocr;

use Illuminate\Support\Str;

class ReceiptAiNormalizerService
{
    public function normalize(array $payload): array
    {
        $structured = $payload['structured'] ?? [];

        $items = collect($structured['items'] ?? [])
            ->map(fn (array $item) => $this->normalizeItem($item))
            ->filter(fn (array $item) => $this->isValidItem($item))
            ->values()
            ->all();

        $itemsTotal = array_sum(array_map(
            fn (array $item) => (int) $item['subtotal'],
            $items
        ));

        $grandTotal = $this->normalizeMoney($structured['grand_total'] ?? 0);
        $notes = array_values(array_filter($structured['notes'] ?? []));

        if ($grandTotal <= 0) {
            $grandTotal = $itemsTotal;
            $notes[] = 'Grand total fallback from item subtotal sum';
        } else {
            $diff = abs($grandTotal - $itemsTotal);

            if ($itemsTotal > 0 && $diff > 10000) {
                $notes[] = 'Grand total mismatch with items sum';
                $grandTotal = $itemsTotal;
            }
        }

        return [
            'engine' => $payload['engine'] ?? 'openai',
            'model' => $payload['model'] ?? null,
            'invoice_number' => $this->nullableString($structured['invoice_number'] ?? null),
            'invoice_date' => $this->nullableString($structured['invoice_date'] ?? null),
            'supplier_name' => $this->nullableString($structured['supplier_name'] ?? null),
            'grand_total' => $grandTotal,
            'items' => $items,
            'notes' => $notes,
            'raw_response_text' => $payload['raw_response_text'] ?? null,
        ];
    }

    protected function normalizeItem(array $item): array
    {
        $nama = trim((string) ($item['nama_material'] ?? ''));
        $satuan = $this->normalizeUnit($item['satuan'] ?? null);

        $qty = $this->normalizeNumber($item['qty'] ?? 0);
        $harga = $this->normalizeMoney($item['harga'] ?? 0);
        $diskon = $this->normalizeDiscount($item['diskon'] ?? null);
        $subtotal = $this->normalizeMoney($item['subtotal'] ?? 0);

        if (!$satuan) {
            [$nama, $satuan] = $this->extractUnitFromName($nama);
        }

        $notes = [];
        $expectedSubtotal = 0;

        if ($qty > 0 && $harga > 0) {
            $expectedSubtotal = (int) round($qty * $harga);
        }

        // hanya infer subtotal kalau subtotal memang kosong
        if ($subtotal <= 0 && $expectedSubtotal > 0) {
            $subtotal = $expectedSubtotal;
            $notes[] = 'Subtotal inferred from qty x harga';
        }

        // hanya infer harga dari subtotal kalau harga kosong
        if ($subtotal > 0 && $qty > 0 && $harga <= 0) {
            $harga = (int) round($subtotal / $qty);
            $notes[] = 'Harga inferred from subtotal / qty';
        }

        // kalau ada diskon, jangan anggap qty x harga harus sama dengan subtotal
        if ($diskon !== null) {
            $notes[] = 'Discount detected';
        } elseif ($subtotal > 0 && $qty > 0 && $harga > 0) {
            $diff = abs($expectedSubtotal - $subtotal);

            if ($diff > 1000) {
                $notes[] = 'Mismatch qty x harga vs subtotal';
            }
        }

        return [
            'kode' => $this->nullableString($item['kode'] ?? null),
            'nama_material' => $nama,
            'satuan' => $satuan,
            'qty' => $qty,
            'harga' => $harga,
            'diskon' => $diskon,
            'subtotal' => $subtotal,
            'raw_line' => trim((string) ($item['raw_line'] ?? '')),
            'confidence' => $this->normalizeFloat($item['confidence'] ?? 0),
            'match' => null,
            'normalizer_notes' => $notes,
        ];
    }

    protected function isValidItem(array $item): bool
    {
        if ($item['nama_material'] === '') {
            return false;
        }

        if ($item['qty'] <= 0 && $item['harga'] <= 0 && $item['subtotal'] <= 0) {
            return false;
        }

        return true;
    }

    protected function normalizeMoney(mixed $value): int
    {
        if (is_int($value) || is_float($value)) {
            return (int) round((float) $value);
        }

        $value = (string) $value;
        $value = str_replace(['Rp', 'rp', 'IDR', 'idr', ' '], '', $value);
        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

        if ($value === '') {
            return 0;
        }

        if (str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        if (str_contains($value, ',')) {
            $parts = explode(',', $value);
            $value = $parts[0];
        }

        return (int) round((float) $value);
    }

    protected function normalizeNumber(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

        if ($value === '' || $value === '.' || $value === ',') {
            return 0;
        }

        $hasDot = str_contains($value, '.');
        $hasComma = str_contains($value, ',');

        if ($hasDot && $hasComma) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);

            return (float) $value;
        }

        if ($hasDot && !$hasComma) {
            $parts = explode('.', $value);

            if (count($parts) > 1) {
                $allThreeDigits = true;

                for ($i = 1; $i < count($parts); $i++) {
                    if (strlen($parts[$i]) !== 3) {
                        $allThreeDigits = false;
                        break;
                    }
                }

                if ($allThreeDigits) {
                    return (float) str_replace('.', '', $value);
                }
            }
        }

        if ($hasComma && !$hasDot) {
            $value = str_replace(',', '.', $value);

            return (float) $value;
        }

        return (float) $value;
    }

    protected function normalizeFloat(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return max(0, min(1, (float) $value));
        }

        $float = $this->normalizeNumber($value);

        return max(0, min(1, $float));
    }

    protected function normalizeDiscount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = $this->normalizeNumber($value);

        return $normalized > 0 ? $normalized : null;
    }

    protected function normalizeUnit(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if (!$value) {
            return null;
        }

        $value = Str::lower(trim($value));

        $map = [
            'pcs' => 'pcs',
            'pcs.' => 'pcs',
            'piece' => 'pcs',
            'lembar' => 'lbr',
            'lbr' => 'lbr',
            'batang' => 'btg',
            'btg' => 'btg',
            'zak' => 'zak',
            'sak' => 'zak',
            'dus' => 'dus',
            'box' => 'box',
            'kg' => 'kg',
            'kilogram' => 'kg',
            'gram' => 'gr',
            'gr' => 'gr',
            'liter' => 'ltr',
            'ltr' => 'ltr',
            'm3' => 'm3',
            'm2' => 'm2',
            'meter' => 'm',
            'mtr' => 'm',
            'm' => 'm',
            'roll' => 'roll',
            'kaleng' => 'klg',
            'klg' => 'klg',
            'set' => 'set',
            'unit' => 'unit',
            'pak' => 'pak',
        ];

        return $map[$value] ?? $value;
    }

    protected function extractUnitFromName(string $nama): array
    {
        $patterns = [
            '/\b(zak|sak)\b/i' => 'zak',
            '/\b(dus|box)\b/i' => 'dus',
            '/\b(lembar|lbr)\b/i' => 'lbr',
            '/\b(batang|btg)\b/i' => 'btg',
            '/\b(kg|kilogram)\b/i' => 'kg',
            '/\b(gr|gram)\b/i' => 'gr',
            '/\b(liter|ltr)\b/i' => 'ltr',
            '/\b(m3)\b/i' => 'm3',
            '/\b(m2)\b/i' => 'm2',
            '/\b(meter|mtr)\b/i' => 'm',
            '/\b(kaleng|klg)\b/i' => 'klg',
            '/\b(pcs|piece)\b/i' => 'pcs',
            '/\b(unit)\b/i' => 'unit',
            '/\b(set)\b/i' => 'set',
            '/\b(pak)\b/i' => 'pak',
        ];

        foreach ($patterns as $pattern => $unit) {
            if (preg_match($pattern, $nama)) {
                return [trim($nama), $unit];
            }
        }

        return [trim($nama), null];
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}