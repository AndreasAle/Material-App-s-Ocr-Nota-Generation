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
        $extracted = $this->openAiReceiptService->extractFromStoredFile($disk, $path);

        $normalized = $this->normalizerService->normalize($extracted);

        $normalized['items'] = $this->matcherService->matchItems($normalized['items']);

        return $normalized;
    }
}