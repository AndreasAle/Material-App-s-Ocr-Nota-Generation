<?php

namespace App\Services\Purchases\Ocr;

use App\Models\ScanCorrection;
use Illuminate\Support\Facades\Log;

class ScanCorrectionService
{
    public function record(
        string $sourceText,
        string $correctedName,
        ?int $materialId,
        ?string $unitName,
        int $userId
    ): void {
        $normalized = $this->normalize($sourceText);

        if ($normalized === '' || trim($correctedName) === '') {
            return;
        }

        try {
            $existing = ScanCorrection::where('source_text_normalized', $normalized)
                ->where('material_id', $materialId)
                ->first();

            if ($existing) {
                $existing->increment('usage_count');
                $existing->update([
                    'corrected_material_name' => trim($correctedName),
                    'corrected_unit_name' => $unitName ? trim($unitName) : $existing->corrected_unit_name,
                    'corrected_by' => $userId,
                ]);
            } else {
                ScanCorrection::create([
                    'source_text' => $sourceText,
                    'source_text_normalized' => $normalized,
                    'material_id' => $materialId,
                    'corrected_material_name' => trim($correctedName),
                    'corrected_unit_name' => $unitName ? trim($unitName) : null,
                    'correction_type' => 'match_override',
                    'corrected_by' => $userId,
                    'usage_count' => 1,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ScanCorrectionService::record failed', [
                'source_text' => $sourceText,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function findMatch(string $sourceText): ?array
    {
        $normalized = $this->normalize($sourceText);

        if ($normalized === '') {
            return null;
        }

        $correction = ScanCorrection::where('source_text_normalized', $normalized)
            ->where('correction_type', '!=', 'deleted')
            ->orderByDesc('usage_count')
            ->first();

        if (! $correction) {
            return null;
        }

        return [
            'material_id' => $correction->material_id,
            'material_name' => $correction->corrected_material_name,
            'unit_name' => $correction->corrected_unit_name,
            'source' => 'learnt',
            'usage_count' => $correction->usage_count,
        ];
    }

    public function totalCorrections(): int
    {
        return ScanCorrection::count();
    }

    public function recentCorrections(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return ScanCorrection::with('corrector:id,name')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9\s]/i', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }
}
