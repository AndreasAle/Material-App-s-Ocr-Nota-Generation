<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PoApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $po,
        public User $approver,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[NOTAGO] PO {$this->po->po_no} menunggu persetujuan",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.po.approval-request');
    }
}
