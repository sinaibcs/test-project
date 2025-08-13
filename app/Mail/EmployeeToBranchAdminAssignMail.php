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

class EmployeeToBranchAdminAssignMail extends Mailable
{
    use Queueable, SerializesModels, MessageTrait;

    public $EmployeeEmail,$EmployeeName,$BranchName,$GlobalSettings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($EmployeeEmail,$EmployeeName,$BranchName,$GlobalSettings)
    {
        $this->EmployeeName = $EmployeeName;
        $this->EmployeeEmail = $EmployeeEmail;
        $this->BranchName = $BranchName;
        $this->GlobalSettings = $GlobalSettings;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address($this->EmployeeRegisterMailFrom, $this->WebSiteName),
            subject: $this->EmployeeToBranchAdminMailSubject,
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
            view: 'mail.EmployeeToBranchAdminAssignMail',
            with: [
                'subject' => $this->EmployeeToBranchAdminMailSubject,
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
