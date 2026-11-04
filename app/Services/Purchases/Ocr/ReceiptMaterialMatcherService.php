<?php

namespace App\Services\Purchases\Ocr;

use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReceiptMaterialMatcherService
{
    public function __construct(
        protected ScanCorrectionService $correctionService,
    ) {}

    public function matchItems(array $items): array
    {
        $materials = Material::query()
            ->select(['id', 'code', 'name', 'unit_id'])
            ->with('unit:id,name,code')
            ->get();

        return collect($items)
            ->map(fn (array $item) => $this->matchSingleItem($item, $materials))
            ->all();
    }

    protected function matchSingleItem(array $item, Collection $materials): array
    {
        $needle = $this->normalizeText($item['nama_material'] ?? '');

        if ($needle === '') {
            $item['match'] = [
                'status' => 'manual_check',
                'material_id' => null,
                'material_code' => null,
                'material_name' => null,
                'score' => 0,
                'source' => 'fuzzy',
            ];

            return $item;
        }

        // Check learnt corrections first — always wins over fuzzy matching
        $learnt = $this->correctionService->findMatch($item['nama_material'] ?? '');

        if ($learnt) {
            $material = $learnt['material_id']
                ? $materials->firstWhere('id', $learnt['material_id'])
                : null;

            $item['match'] = [
                'status' => 'matched',
                'material_id' => $learnt['material_id'],
                'material_code' => $material?->code ?? null,
                'material_name' => $learnt['material_name'],
                'score' => 1.0,
                'source' => 'learnt',
                'usage_count' => $learnt['usage_count'],
            ];

            return $item;
        }

        // Fuzzy matching against material database
        $best = null;
        $bestScore = 0;

        foreach ($materials as $material) {
            $candidate = $this->normalizeText($material->name ?? '');
            $score = $this->similarityScore($needle, $candidate);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $material;
            }
        }

        $item['match'] = [
            'status' => $best && $bestScore >= 0.60 ? 'matched' : 'manual_check',
            'material_id' => $best && $bestScore >= 0.60 ? $best->id : null,
            'material_code' => $best && $bestScore >= 0.60 ? ($best->code ?? null) : null,
            'material_name' => $best && $bestScore >= 0.60 ? ($best->name ?? null) : null,
            'score' => round($bestScore, 4),
            'source' => 'fuzzy',
        ];

        return $item;
    }

    protected function normalizeText(string $value): string
    {
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    protected function similarityScore(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0;
        }

        similar_text($a, $b, $percent);
        $score1 = $percent / 100;

        $tokensA = collect(explode(' ', $a))->filter()->values();
        $tokensB = collect(explode(' ', $b))->filter()->values();

        if ($tokensA->isEmpty() || $tokensB->isEmpty()) {
            return $score1;
        }

        $intersection = $tokensA->intersect($tokensB)->count();
        $union = $tokensA->unique()->count() + $tokensB->unique()->count() - $intersection;
        $score2 = $union > 0 ? $intersection / $union : 0;

        return max($score1, $score2);
    }
}
