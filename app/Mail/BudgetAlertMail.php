<?php

namespace App\Mail;

use App\Models\RabPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RabPlan $rabPlan,
        public float $utilizationPercent,
        public float $totalBudget,
        public float $totalActual,
    ) {}

    public function envelope(): Envelope
    {
        $pct = number_format($this->utilizationPercent, 1);
        return new Envelope(
            subject: "[NOTAGO] Peringatan Anggaran: RAB {$this->rabPlan->name} sudah terpakai {$pct}%",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rab.budget-alert');
    }
}
