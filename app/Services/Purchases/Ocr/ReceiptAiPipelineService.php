<?php

namespace App\Services\Purchases\Ocr;

class ReceiptAiPipelineService
{
    public function __construct(
        protected OpenAiReceiptService $openAiReceiptService,
        protected ReceiptAiNormalizerService $normalizerService,
        protected ReceiptMaterialMatcherService $matcherService,
    ) {
    }

    public function processStoredFile(string $disk, string $path): array
    {
        $started = hrtime(true);
        $extracted = $this->openAiReceiptService->extractFromStoredFile($disk, $path);
        $extractedAt = hrtime(true);

        $normalized = $this->normalizerService->normalize($extracted);
        $normalizedAt = hrtime(true);

        $normalized['items'] = $this->matcherService->matchItems($normalized['items']);
        $finished = hrtime(true);
        $normalized['timings_ms'] = [
            'extraction' => round(($extractedAt - $started) / 1e6),
            'normalization' => round(($normalizedAt - $extractedAt) / 1e6),
            'matching' => round(($finished - $normalizedAt) / 1e6),
            'total' => round(($finished - $started) / 1e6),
        ];

        return $normalized;
    }
}
