<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $companyName,
        public readonly string $loginUrl,
        public readonly string $trialEndsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 MİYSOFT PTS\'ye Hoş Geldiniz!',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome');
    }
}
