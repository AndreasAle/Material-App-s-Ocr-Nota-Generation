<?php

namespace Tests\Unit;

use App\Services\Purchases\Ocr\ReceiptAiNormalizerService;
use PHPUnit\Framework\TestCase;

class ReceiptAiNormalizerTest extends TestCase
{
    public function test_discrepant_total_is_preserved_instead_of_forcing_a_balance(): void
    {
        $result = (new ReceiptAiNormalizerService)->normalize(['structured' => [
            'grand_total' => 2407000,
            'items' => array_map(fn ($amount) => [
                'nama_material' => 'Material', 'qty' => 1, 'harga' => $amount,
                'subtotal' => $amount, 'confidence' => 0.95,
            ], [900000, 60000, 475000, 39000, 75000, 87500, 85000, 270000, 112500, 51000, 70000, 90000, 30000, 150000]),
        ]]);

        // User-confirmed amounts are a normalization fixture, not an OCR accuracy test.
        $this->assertSame(2495000, array_sum(array_column($result['items'], 'subtotal')));
        $this->assertSame(2407000, $result['grand_total']);
        $this->assertSame(2407000, $result['detected_grand_total']);
        $this->assertContains('Grand total mismatch with items sum', $result['notes']);
    }

    public function test_readable_item_with_unreadable_numbers_is_kept_for_review(): void
    {
        $result = (new ReceiptAiNormalizerService)->normalize(['structured' => [
            'grand_total' => 0,
            'items' => [['nama_material' => 'Majun warna', 'qty' => null, 'harga' => null, 'subtotal' => null, 'confidence' => 0.4]],
        ]]);
        $this->assertCount(1, $result['items']);
        $this->assertNotEmpty($result['items'][0]['review_reasons']);
        $this->assertSame(0, $result['items'][0]['subtotal']);
        $this->assertSame(0, $result['detected_grand_total']);
    }

    public function test_inferred_price_is_identified_and_written_subtotal_preserved(): void
    {
        $result = (new ReceiptAiNormalizerService)->normalize(['structured' => [
            'items' => [['nama_material' => 'Seal tape', 'qty' => 1, 'harga' => null, 'subtotal' => 39000, 'confidence' => 0.95]],
        ]]);
        $item = $result['items'][0];
        $this->assertSame(39000, $item['harga']);
        $this->assertSame(39000, $item['subtotal']);
        $this->assertNotEmpty($item['review_reasons']);
        $this->assertSame(0, $result['detected_grand_total']);
        $this->assertSame(39000, $result['grand_total']);
    }

    public function test_inconsistent_row_is_flagged_without_replacing_amount(): void
    {
        $result = (new ReceiptAiNormalizerService)->normalize(['structured' => [
            'items' => [['nama_material' => 'Rexco 18', 'qty' => 6, 'harga' => 49500, 'subtotal' => 270000, 'confidence' => 0.99]],
        ]]);
        $item = $result['items'][0];
        $this->assertSame(270000, $item['subtotal']);
        $this->assertContains('Mismatch qty x harga vs subtotal', $item['normalizer_notes']);
        $this->assertNotEmpty($item['review_reasons']);
    }
}
