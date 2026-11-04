<?php

namespace App\Mail;

use App\Models\PurchaseTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseApprovalResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseTransaction $transaction,
        public string $result, // 'approved' | 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->result === 'approved' ? 'Disetujui' : 'Ditolak';
        return new Envelope(subject: "[NOTAGO] Transaksi {$this->transaction->transaction_no} {$label}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.purchase.approval-result');
    }
}
