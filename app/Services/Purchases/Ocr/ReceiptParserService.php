<?php

namespace App\Services\Purchases\Ocr;

use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReceiptParserService
{
    /**
     * Alias peran kolom yang mungkin muncul di berbagai nota.
     */
    protected array $headerAliases = [
        'name' => [
            'jenis', 'nama barang', 'nama material', 'barang', 'item', 'uraian', 'deskripsi', 'produk', 'material',
        ],
        'attribute' => [
            'specif', 'spec', 'spesifikasi', 'merk', 'merek', 'type', 'tipe', 'ukuran', 'dimensi', 'warna', 'grade', 'keterangan',
        ],
        'unit' => [
            'satuan', 'unit', 'uom', 'sat',
        ],
        'qty' => [
            'qty', 'kuantitas', 'jumlah qty', 'banyak', 'volume', 'vol', 'kuantiti',
        ],
        'price' => [
            'harga', 'harga/sat', 'harga satuan', 'harga sat', 'unit price', 'rate', 'price',
        ],
        'subtotal' => [
            'jumlah', 'subtotal', 'total', 'amount', 'jml',
        ],
    ];

    protected array $validUnits = [
        'm3', 'm2', 'kg', 'gr', 'gram', 'klg', 'lbr', 'lembar', 'pcs', 'pc', 'unit',
        'batang', 'sak', 'zak', 'dus', 'roll', 'liter', 'ltr', 'lsn', 'set', 'box',
    ];

    public function parse(string $rawText): array
    {
        $text = $this->normalizeRawText($rawText);

        $noteNumber = $this->extractNoteNumberFromText($text);
        $transactionDate = $this->extractDateFromText($text);

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_values(array_filter(array_map([$this, 'cleanLine'], $lines)));

        $items = [];

        // 1. Coba parse berbasis tabel/header dulu
        $items = $this->parseByDetectedTable($lines);

        // 2. Kalau hasil sedikit / kosong, fallback ke parse baris item biasa
        if (count($items) === 0) {
            $candidateLines = $this->buildCandidateLines($lines);

            foreach ($candidateLines as $line) {
                $parsed = $this->parseFallbackItemLine($line);

                if ($parsed) {
                    $items[] = $parsed;
                }
            }
        }

        $items = $this->normalizeItems($items);

        return [
            'note_number' => $noteNumber,
            'transaction_date' => $transactionDate,
            'items' => $items,
        ];
    }

    protected function normalizeRawText(string $text): string
    {
        $replace = [
            "\t" => ' ',
            '|' => ' | ',
            '—' => '-',
            '–' => '-',
            '“' => '"',
            '”' => '"',
            '‘' => "'",
            '’' => "'",
            'O8' => '08',
            'o8' => '08',
            'kg.' => 'kg',
            'klg.' => 'klg',
            'lbr.' => 'lbr',
            'm 3' => 'm3',
            'm-3' => 'm3',
            'rp.' => 'Rp',
            'r p' => 'Rp',
            'harga sat' => 'harga/sat',
            'harga / sat' => 'harga/sat',
            'kuantitas' => 'kuantitas',
            'specif.' => 'specif',
        ];

        $text = str_ireplace(array_keys($replace), array_values($replace), $text);
        $text = preg_replace('/[ ]{2,}/', ' ', $text);
        $text = preg_replace('/\s+Rp\s+/i', ' Rp ', $text);

        return trim((string) $text);
    }

    protected function cleanLine(string $line): string
    {
        $line = trim($line);
        $line = preg_replace('/\s+/', ' ', $line);

        return trim((string) $line);
    }

    protected function extractNoteNumberFromText(string $text): ?string
    {
        $patterns = [
            '/NO\.?\s*SPP\s*[:\-]?\s*([A-Z0-9\-\/\.]+)/i',
            '/(?:NO\.?|NOMOR|NO SURAT|NO PESANAN|INVOICE|NOTA)\s*[:\-]?\s*([A-Z0-9\-\/\.]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return strtoupper(trim((string) $m[1]));
            }
        }

        return null;
    }

    protected function extractDateFromText(string $text): ?string
    {
        $months = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\s+(\d{2,4})/i', $text, $m)) {
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }

            return sprintf(
                '%04d-%02d-%02d',
                $year,
                $months[Str::lower($m[2])] ?? 1,
                (int) $m[1]
            );
        }

        return null;
    }

    /**
     * Tahap 1:
     * Cari header tabel yang paling mungkin.
     */
    protected function parseByDetectedTable(array $lines): array
    {
        $headerIndex = null;
        $headerRoles = null;

        foreach ($lines as $index => $line) {
            $detected = $this->detectHeaderRoles($line);

            if (count($detected) >= 4) {
                $headerIndex = $index;
                $headerRoles = $detected;
                break;
            }
        }

        if ($headerIndex === null) {
            return [];
        }

        $items = [];

        for ($i = $headerIndex + 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            if ($this->looksLikeFooterOrEnd($line)) {
                break;
            }

            if (! $this->looksLikeItemLine($line)) {
                continue;
            }

            $parsed = $this->parseTableRow($line, $headerRoles);

            if ($parsed) {
                $items[] = $parsed;
            }
        }

        return $items;
    }

    /**
     * Deteksi peran header dari satu baris header.
     */
    protected function detectHeaderRoles(string $line): array
    {
        $normalized = Str::lower($line);
        $roles = [];

        foreach ($this->headerAliases as $role => $aliases) {
            foreach ($aliases as $alias) {
                if (Str::contains($normalized, Str::lower($alias))) {
                    $roles[] = $role;
                    break;
                }
            }
        }

        return array_values(array_unique($roles));
    }

    protected function looksLikeFooterOrEnd(string $line): bool
    {
        $line = Str::lower(trim($line));

        if ($line === '') {
            return true;
        }

        $footerWords = [
            'jumlah',
            'grand total',
            'total',
            'syarat penyerahan',
            'barang harus',
            'barang dikirim',
            'wates',
            'winarto',
            'muh.',
            'zaini',
            'amin',
        ];

        foreach ($footerWords as $word) {
            if (Str::contains($line, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build candidate baris TANPA merge line.
     * Ini penting supaya tidak double-double.
     */
    protected function buildCandidateLines(array $lines): array
    {
        $candidates = [];

        foreach ($lines as $line) {
            if ($this->shouldSkipLine($line)) {
                continue;
            }

            $line = $this->normalizeLineForParsing($line);

            if ($this->looksLikeItemLine($line)) {
                $candidates[] = $line;
            }
        }

        return array_values(array_unique($candidates));
    }

    protected function normalizeLineForParsing(string $line): string
    {
        $line = preg_replace('/\s+/', ' ', $line);
        $line = trim((string) $line);

        return $line;
    }

    protected function shouldSkipLine(string $line): bool
    {
        $line = Str::lower(trim($line));

        if ($line === '' || mb_strlen($line) < 3) {
            return true;
        }

        $skipWords = [
            'surat pesanan pembelian',
            'surat pesanan',
            'pesanan pembelian',
            'mohon dikirimkan',
            'syarat penyerahan',
            'barang harus',
            'barang dikirim',
            'no.spp',
            'tgl',
            'sifat',
            'kuantitas',
            'harga/sat',
            'jenis',
            'satuan',
            'specif',
            'subtotal',
            'grand total',
            'jumlah',
            'total',
        ];

        foreach ($skipWords as $word) {
            if (Str::contains($line, $word)) {
                return true;
            }
        }

        if (preg_match('/^[^a-z0-9]+$/i', $line)) {
            return true;
        }

        return false;
    }

    protected function looksLikeItemLine(string $line): bool
    {
        preg_match_all('/\d[\d\.\,]*/', $line, $numbers);
        $numberCount = count($numbers[0] ?? []);

        $hasUnit = preg_match('/\b(' . implode('|', array_map('preg_quote', $this->validUnits)) . ')\b/i', $line) === 1;

        return $hasUnit && $numberCount >= 3;
    }

    /**
     * Parse utama untuk row tabel.
     * Fokus: baca struktur manusiawi nota.
     */
    protected function parseTableRow(string $line, array $headerRoles = []): ?array
    {
        $line = trim($line);

        // Bersihkan nomor urut depan
        $line = preg_replace('/^\s*\d+\s+/', '', $line);

        // Cegah line gabungan beberapa item
        if ($this->looksLikeCombinedItems($line)) {
            return null;
        }

        // Pola utama:
        // [nama + atribut] [unit] [qty] [price] [subtotal]
        $unitPattern = implode('|', array_map(fn ($u) => preg_quote($u, '/'), $this->validUnits));

        $patterns = [
            '/^(?P<desc>.+?)\s+(?P<unit>' . $unitPattern . ')\s+(?P<qty>\d+(?:[\,\.]\d+)?)\s+(?:Rp\s*)?(?P<price>\d[\d\.\,]*)\s+(?:Rp\s*)?(?P<subtotal>\d[\d\.\,]*)$/iu',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $line, $m)) {
                continue;
            }

            $descRaw = trim((string) ($m['desc'] ?? ''));
            $unit = $this->normalizeUnit((string) ($m['unit'] ?? ''));
            $qty = $this->normalizeQty((string) ($m['qty'] ?? '0'));
            $price = $this->normalizeMoney((string) ($m['price'] ?? '0'));
            $subtotal = $this->normalizeMoney((string) ($m['subtotal'] ?? '0'));

            if (! $this->isValidQty($qty) || ! $this->isValidMoney($price) || ! $this->isValidMoney($subtotal)) {
                continue;
            }

            if (! $this->isSubtotalReasonable($qty, $price, $subtotal)) {
                continue;
            }

            [$baseName, $attributeText, $notes] = $this->splitDescriptionHumanly($descRaw, $headerRoles);

            $materialName = $this->buildMaterialName($baseName, $attributeText);

            if (! $this->isValidMaterialName($materialName)) {
                continue;
            }

            $matchedMaterial = $this->matchMaterial($materialName, $unit);

            return [
                'material_id' => $matchedMaterial?->id,
                'material_code' => $matchedMaterial?->code,
                'material_name_snapshot' => $materialName,
                'unit_name_snapshot' => $matchedMaterial?->unit?->name ?? $unit,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => $notes !== '' ? $notes : 'Hasil OCR parse tabel',
                'matched' => (bool) $matchedMaterial,
                'raw_line' => $line,
            ];
        }

        return $this->parseFallbackItemLine($line);
    }

    /**
     * Fallback parser kalau struktur tabel tidak sempurna.
     */
    protected function parseFallbackItemLine(string $line): ?array
    {
        $line = trim($line);
        $line = preg_replace('/^\s*\d+\s+/', '', $line);

        if ($this->looksLikeCombinedItems($line)) {
            return null;
        }

        preg_match_all('/\d[\d\.\,]*/', $line, $m);
        $numbers = $m[0] ?? [];

        if (count($numbers) < 3) {
            return null;
        }

        preg_match('/\b(' . implode('|', array_map('preg_quote', $this->validUnits)) . ')\b/i', $line, $unitMatch);

        $unit = $this->normalizeUnit((string) ($unitMatch[1] ?? 'unit'));

        $priceRaw = $numbers[count($numbers) - 2] ?? null;
        $subtotalRaw = $numbers[count($numbers) - 1] ?? null;
        $qtyRaw = $numbers[count($numbers) - 3] ?? null;

        if (! $qtyRaw || ! $priceRaw || ! $subtotalRaw) {
            return null;
        }

        $qty = $this->normalizeQty($qtyRaw);
        $price = $this->normalizeMoney($priceRaw);
        $subtotal = $this->normalizeMoney($subtotalRaw);

        if (! $this->isValidQty($qty) || ! $this->isValidMoney($price) || ! $this->isValidMoney($subtotal)) {
            return null;
        }

        if (! $this->isSubtotalReasonable($qty, $price, $subtotal)) {
            return null;
        }

        $desc = preg_replace('/\b' . preg_quote($unit, '/') . '\b.*$/iu', '', $line);
        $desc = trim((string) $desc);

        [$baseName, $attributeText, $notes] = $this->splitDescriptionHumanly($desc, []);

        $materialName = $this->buildMaterialName($baseName, $attributeText);

        if (! $this->isValidMaterialName($materialName)) {
            return null;
        }

        $matchedMaterial = $this->matchMaterial($materialName, $unit);

        return [
            'material_id' => $matchedMaterial?->id,
            'material_code' => $matchedMaterial?->code,
            'material_name_snapshot' => $materialName,
            'unit_name_snapshot' => $matchedMaterial?->unit?->name ?? $unit,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal,
            'notes' => $notes !== '' ? $notes : 'Hasil OCR parse fallback',
            'matched' => (bool) $matchedMaterial,
            'raw_line' => $line,
        ];
    }

    /**
     * Logika "lebih manusiawi":
     * - nama inti barang dipertahankan
     * - atribut tambahan seperti spec/merk/type/ukuran digabung
     * - catatan terlalu panjang masuk notes
     */
    protected function splitDescriptionHumanly(string $descRaw, array $headerRoles = []): array
    {
        $descRaw = trim($descRaw);
        $descRaw = preg_replace('/\s+/', ' ', $descRaw);

        // Kalau ada pemisah pipe dari OCR
        if (Str::contains($descRaw, '|')) {
            $parts = array_values(array_filter(array_map('trim', explode('|', $descRaw)), fn ($v) => $v !== ''));

            if (count($parts) >= 2) {
                $baseName = (string) ($parts[0] ?? '');
                $attributeText = implode(' ', array_slice($parts, 1));

                return [
                    $this->cleanMaterialText($baseName),
                    $this->cleanMaterialText($attributeText),
                    '',
                ];
            }
        }

        // Jika nama + spec dipisah spasi normal, pertahankan apa adanya
        // Tidak normalisasi keras.
        $tokens = preg_split('/\s+/', $descRaw) ?: [];

        // Jika tidak banyak token, langsung jadikan nama
        if (count($tokens) <= 4) {
            return [
                $this->cleanMaterialText($descRaw),
                '',
                '',
            ];
        }

        // Coba deteksi atribut umum di bagian akhir
        $attributeHints = [
            'kw-1', 'kw-2', 'kw-3', 'super', 'premium', 'lokal',
        ];

        $tail = Str::lower(implode(' ', array_slice($tokens, -3)));
        foreach ($attributeHints as $hint) {
            if (Str::contains($tail, $hint)) {
                $baseName = implode(' ', array_slice($tokens, 0, -1));
                $attribute = end($tokens);

                return [
                    $this->cleanMaterialText($baseName),
                    $this->cleanMaterialText((string) $attribute),
                    '',
                ];
            }
        }

        // Default: pertahankan seluruh desc sebagai nama barang
        return [
            $this->cleanMaterialText($descRaw),
            '',
            '',
        ];
    }

    protected function buildMaterialName(string $baseName, string $attributeText = ''): string
    {
        $baseName = trim($baseName);
        $attributeText = trim($attributeText);

        if ($attributeText === '' || $attributeText === '-' || $attributeText === '--') {
            return $baseName;
        }

        return trim($baseName . ' ' . $attributeText);
    }

    protected function cleanMaterialText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/^[\-\.,\/\s]+|[\-\.,\/\s]+$/', '', (string) $text);

        return trim((string) $text);
    }

    /**
     * Deteksi kalau satu line berisi lebih dari satu item.
     * Ini sumber dobel paling umum.
     */
    protected function looksLikeCombinedItems(string $line): bool
    {
        // Lebih dari satu satuan valid
        preg_match_all('/\b(' . implode('|', array_map('preg_quote', $this->validUnits)) . ')\b/i', $line, $units);
        if (count($units[0] ?? []) > 1) {
            return true;
        }

        // Ada nomor urut baru muncul di tengah line
        if (preg_match('/\s+\d+\s+[A-Za-z]/', $line)) {
            return true;
        }

        // Terlalu banyak angka besar => kemungkinan gabungan beberapa row
        preg_match_all('/\b\d{1,3}(?:\.\d{3})+\b/', $line, $moneyMatches);
        if (count($moneyMatches[0] ?? []) > 2) {
            return true;
        }

        return false;
    }

    protected function normalizeUnit(string $unit): string
    {
        $unit = Str::lower(trim($unit));

        return match ($unit) {
            'lembar' => 'lbr',
            'pc' => 'pcs',
            'ltr' => 'liter',
            default => $unit,
        };
    }

    protected function normalizeQty(string $value): float
    {
        $value = trim($value);

        if (substr_count($value, ',') === 1 && substr_count($value, '.') === 0) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        return (float) $value;
    }

    protected function normalizeMoney(string $value): float
    {
        $value = trim($value);
        $value = preg_replace('/[^\d\.]/', '', $value);
        $value = str_replace('.', '', $value);

        return (float) $value;
    }

    protected function isValidMaterialName(string $name): bool
    {
        if ($name === '' || mb_strlen($name) < 2) {
            return false;
        }

        $lower = Str::lower($name);

        $bad = [
            'jumlah',
            'total',
            'subtotal',
            'harga',
            'kuantitas',
            'satuan',
            'surat pesanan',
        ];

        foreach ($bad as $word) {
            if ($lower === $word) {
                return false;
            }
        }

        return preg_match('/[a-z]/i', $name) === 1;
    }

    protected function isValidQty(float $qty): bool
    {
        return $qty > 0 && $qty <= 100000;
    }

    protected function isValidMoney(float $value): bool
    {
        return $value >= 0 && $value <= 1000000000;
    }

    protected function isSubtotalReasonable(float $qty, float $price, float $subtotal): bool
    {
        $expected = $qty * $price;
        $diff = abs($expected - $subtotal);

        return $diff <= max(2000, $expected * 0.15);
    }

    /**
     * Matching ke tabel sistem hanya OPSIONAL.
     * Snapshot nama dari nota tetap dipertahankan.
     */
    protected function matchMaterial(string $name, string $unit): ?Material
    {
        /** @var Collection<int, Material> $materials */
        $materials = Material::query()
            ->with('unit')
            ->where('is_active', true)
            ->get();

        $needle = Str::lower(trim($name));
        $best = null;
        $bestScore = 0.0;

        foreach ($materials as $material) {
            $candidate = Str::lower(trim((string) $material->name));
            similar_text($needle, $candidate, $percent);

            if (Str::contains($needle, $candidate) || Str::contains($candidate, $needle)) {
                $percent += 12;
            }

            $materialUnit = Str::lower((string) optional($material->unit)->name);
            if ($materialUnit !== '' && $materialUnit === $unit) {
                $percent += 8;
            }

            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $material;
            }
        }

        return $bestScore >= 70 ? $best : null;
    }

    protected function normalizeItems(array $items): array
    {
        $clean = [];
        $seen = [];

        foreach ($items as $item) {
            if (
                empty($item['material_name_snapshot']) ||
                ($item['qty'] ?? 0) <= 0 ||
                ($item['price'] ?? 0) < 0 ||
                ($item['subtotal'] ?? 0) < 0
            ) {
                continue;
            }

            $name = trim((string) ($item['material_name_snapshot'] ?? ''));

            if ($this->looksLikeCombinedItems((string) ($item['raw_line'] ?? $name))) {
                continue;
            }

            $fingerprint = md5(json_encode([
                Str::lower($name),
                Str::lower((string) ($item['unit_name_snapshot'] ?? '')),
                (float) ($item['qty'] ?? 0),
                (float) ($item['price'] ?? 0),
                (float) ($item['subtotal'] ?? 0),
            ]));

            if (isset($seen[$fingerprint])) {
                continue;
            }

            $seen[$fingerprint] = true;
            $clean[] = $item;
        }

        return array_values($clean);
    }
}