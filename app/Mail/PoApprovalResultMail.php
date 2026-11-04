<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PoApprovalResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseOrder $po,
        public string $result, // 'approved' | 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->result === 'approved' ? 'Disetujui' : 'Ditolak';
        return new Envelope(subject: "[NOTAGO] PO {$this->po->po_no} {$label}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.po.approval-result');
    }
}
