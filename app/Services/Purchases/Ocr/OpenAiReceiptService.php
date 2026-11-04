<?php

namespace App\Services\Purchases\Ocr;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;
use Throwable;

class OpenAiReceiptService
{
    public function extractFromStoredFile(string $disk, string $path): array
    {
        if (!config('receipt_ai.enabled')) {
            throw new RuntimeException('Receipt AI is disabled.');
        }

        if (!Storage::disk($disk)->exists($path)) {
            throw new RuntimeException("Receipt file not found: {$disk}:{$path}");
        }

        $absolutePath = Storage::disk($disk)->path($path);

        return $this->extractFromAbsolutePath($absolutePath);
    }

    public function extractFromAbsolutePath(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException("Receipt file not found: {$absolutePath}");
        }

        $mimeType = mime_content_type($absolutePath) ?: 'image/jpeg';
        $base64 = base64_encode(file_get_contents($absolutePath));

        $schema = $this->schema();

        $input = [
            [
                'role' => 'system',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $this->systemPrompt(),
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $this->userPrompt(),
                    ],
                    [
                        'type' => 'input_image',
                        'image_url' => "data:{$mimeType};base64,{$base64}",
                        'detail' => 'high',
                    ],
                ],
            ],
        ];

        try {
            $response = OpenAI::responses()->create([
                'model' => config('receipt_ai.model'),
                'input' => $input,
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'receipt_extraction',
                        'strict' => true,
                        'schema' => $schema,
                    ],
                ],
            ]);

            $rawText = $response->outputText ?? null;

            if (!is_string($rawText) || trim($rawText) === '') {
                throw new RuntimeException('OpenAI returned empty structured output.');
            }

            $decoded = json_decode($rawText, true);

            if (!is_array($decoded)) {
                throw new RuntimeException('OpenAI structured output is not valid JSON.');
            }

            return [
                'engine' => 'openai',
                'model' => config('receipt_ai.model'),
                'raw_response_text' => $rawText,
                'structured' => $decoded,
            ];
        } catch (Throwable $e) {
            Log::error('OpenAI receipt extraction failed.', [
                'message' => $e->getMessage(),
                'file' => $absolutePath,
            ]);

            throw $e;
        }
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
You extract purchase receipt / invoice data from Indonesian handwritten or printed receipts.

Your task is to read the receipt image and convert it into structured transaction data.

The receipt may contain a TABLE with columns such as:
- Banyaknya / Qty
- Nama Barang
- Merk
- Satuan
- Harga
- Diskon
- Jumlah / Total

You must read EACH ROW of the item table carefully from left to right.

Rules:
1. Focus only on visible item rows inside the receipt table.
2. Ignore signatures, stamps, shadows, handwritten checkmarks, page folds, and background papers.
3. Do not merge two different rows into one item.
4. For each row, extract:
   - kode
   - nama_material
   - satuan
   - qty
   - harga
   - diskon
   - subtotal
   - raw_line
   - confidence
5. If no item code is visible, use null for kode.
6. If the unit is unclear, use null. Do not guess random units.
7. qty, harga, diskon, subtotal must be numeric only, without currency symbols or separators.
For Indonesian invoices, quantity values like 12.000, 4.000, 3.000 must be interpreted as thousand-separated integers, meaning 12000, 4000, 3000.
Do not interpret 12.000 as decimal 12.
When unsure, preserve the original quantity text meaning from the Qty column.
8. If the receipt has a rightmost "Total" or "Jumlah" column, prioritize that value as subtotal.
9. If a row includes discount percentage/value, put it into diskon.
10. If subtotal is clearly visible, DO NOT recompute it from qty × harga because discount may apply.
11. Only infer subtotal from qty × harga when the subtotal/total column is missing.
12. grand_total must be taken from the visible final total at the bottom of the receipt when readable.
13. If the visible grand total is unclear, use the sum of valid row subtotals.
14. Do not invent items that are not clearly visible.
15. If a row is too unclear, omit it instead of guessing.
16. invoice_number, invoice_date, supplier_name may be null when unreadable.
17. Return only valid JSON that follows the schema exactly.
PROMPT;
    }

    protected function userPrompt(): string
    {
        return <<<PROMPT
Please read this Indonesian receipt or invoice image carefully.

Important extraction instructions:
- Read the receipt as a TABLE row by row.
- Separate qty, item name, unit, unit price, discount, and subtotal by column position.
- Prioritize the "Total" or "Jumlah" column for each row subtotal.
- If a discount column exists, extract it into diskon.
- Do not force subtotal = qty × harga if discount is present.
- Prioritize the final bottom total for grand_total if readable.
- Do not guess values from shadows, handwritten ticks, or background paper.
- If unit is unclear, return null for satuan.
Important:
- In Indonesian invoice tables, qty values such as 12.000 or 4.000 usually mean 12000 or 4000, not decimal values.
- Treat dots in the Qty column as thousand separators unless the document clearly shows decimal quantities.
- Output must follow the required JSON schema exactly.
PROMPT;
    }

    protected function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'invoice_number' => [
                    'type' => ['string', 'null'],
                ],
                'invoice_date' => [
                    'type' => ['string', 'null'],
                ],
                'supplier_name' => [
                    'type' => ['string', 'null'],
                ],
                'grand_total' => [
                    'type' => 'number',
                ],
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'kode' => [
                                'type' => ['string', 'null'],
                            ],
                            'nama_material' => [
                                'type' => 'string',
                            ],
                            'satuan' => [
                                'type' => ['string', 'null'],
                            ],
                            'qty' => [
                                'type' => ['string', 'number', 'null'],
                            ],
                            'harga' => [
                                'type' => ['string', 'number', 'null'],
                            ],
                            'diskon' => [
                                'type' => ['string', 'number', 'null'],
                            ],
                            'subtotal' => [
                                'type' => ['string', 'number', 'null'],
                            ],
                            'raw_line' => [
                                'type' => 'string',
                            ],
                            'confidence' => [
                                'type' => 'number',
                            ],
                        ],
                        'required' => [
                            'kode',
                            'nama_material',
                            'satuan',
                            'qty',
                            'harga',
                            'diskon',
                            'subtotal',
                            'raw_line',
                            'confidence',
                        ],
                    ],
                ],
                'notes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'required' => [
                'invoice_number',
                'invoice_date',
                'supplier_name',
                'grand_total',
                'items',
                'notes',
            ],
        ];
    }
}