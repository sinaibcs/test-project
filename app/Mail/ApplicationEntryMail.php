<?php

namespace App\Mail;

use App\Models\AllowanceProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationEntryMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email,$tracking_no,$mobile,$message,$name,$program_name;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->email = $data->email;
        $this->tracking_no = $data->application_id;
        $this->name = $data->name_en;
        $this->mobile = $data->mobile;

        // Fetch the program name from the Program model using program_id
       $program = AllowanceProgram::find($data->program_id);
       $this->program_name = $program ? $program->name_en : 'Unknown Program';

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
          subject: 'Online Application for the ' . $this->program_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.ApplicationEntryMail',
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