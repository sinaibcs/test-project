<?php

namespace App\Mail;

use App\Http\Traits\MessageTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MerchantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels, MessageTrait;
    public $merchantWelcomeMail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($merchantWelcomeMail)
    {
        $this->merchantWelcomeMail = $merchantWelcomeMail;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address($this->InfoMailFrom, $this->WebSiteName),
            subject: $this->EmployeeRegisterMailSubject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mail.MerchantWelcomeMail',
            with: [
                'subject' => $this->EmployeeRegisterMailSubject,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
