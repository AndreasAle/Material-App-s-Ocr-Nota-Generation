<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public bool $isOverdue = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isOverdue
            ? "[NOTAGO] Invoice {$this->invoice->invoice_no} TERLAMBAT dibayar"
            : "[NOTAGO] Invoice {$this->invoice->invoice_no} jatuh tempo besok";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice.due');
    }
}
