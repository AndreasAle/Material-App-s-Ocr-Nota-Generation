<?php

namespace App\Mail;

use App\Models\PurchaseTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseTransaction $transaction,
        public User $approver,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[NOTAGO] Transaksi {$this->transaction->transaction_no} menunggu persetujuan",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.purchase.approval-request');
    }
}
