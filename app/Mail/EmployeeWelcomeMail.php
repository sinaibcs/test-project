<?php

namespace App\Mail;

use App\Http\Traits\MessageTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeWelcomeMail extends Mailable
{
    use Queueable, SerializesModels, MessageTrait;
    public $EmployeeEmail,$EmployeeName,$EmployeePassword,$GlobalSettings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($EmployeeEmail,$EmployeeName,$EmployeePassword,$GlobalSettings)
    {
        $this->EmployeeName = $EmployeeName;
        $this->EmployeeEmail = $EmployeeEmail;
        $this->EmployeePassword = $EmployeePassword;
        $this->GlobalSettings = $GlobalSettings;
    }

    public function build()
    {
        return $this->from($this->EmployeeRegisterMailFrom,$this->EmployeeRegisterMailName)->view('mail.EmployeeWelcomeMail')
            ->subject($this->EmployeeRegisterMailSubject)->with([
                'EmployeeName'  => $this->EmployeeName,
                'EmployeeEmail' => $this->EmployeeEmail,
                'EmployeePassword' => $this->EmployeePassword,
                'GlobalSettings' => $this->GlobalSettings
            ]);
    }
}
