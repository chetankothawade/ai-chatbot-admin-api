<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $frontendUrl;

    public function __construct($token, $email, $frontendUrl)
    {
        $this->token = $token;
        $this->email = $email;
        $this->frontendUrl = $frontendUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'url' => "{$this->frontendUrl}/reset-password?token={$this->token}&email={$this->email}",
                'brand_logo' => asset('images/logo.png'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
