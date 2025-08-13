<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BeneficiaryCreateMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email,$name,$program;
    /**
     * Create a new message instance.
     */
    public function __construct($email,$name,$program)
    {
        $this->email = $email;
        $this->name = $name;
        $this->program = $program;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
         $subject = 'Congratulations! You are beneficiary of ' . $this->program;

        return new Envelope(
            subject: $subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.beneficiaryCreateMail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
