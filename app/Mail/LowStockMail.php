<?php

namespace App\Mail;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<array{material: Material, current_stock: float, minimum_stock: float}> $lowStockItems
     */
    public function __construct(
        public array $lowStockItems,
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->lowStockItems);
        return new Envelope(
            subject: "[NOTAGO] Peringatan: {$count} Material Stok Menipis",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.stock.low-stock');
    }
}
