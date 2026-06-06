<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpenseApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $personelName,
        public readonly float  $amount,
        public readonly string $currency,
        public readonly string $categoryName,
        public readonly string $approverName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Masraf Talebiniz Onaylandı — MİYSOFT PTS',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.expense-approved');
    }
}
