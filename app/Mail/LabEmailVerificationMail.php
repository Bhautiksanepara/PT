<?php

namespace App\Mail;

use App\Models\LabSignupVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LabEmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LabSignupVerification $verification, public string $otp)
    {
    }

    public function build()
    {
        return $this->subject('Verify your PT Software laboratory email')
            ->view('emails.lab_email_verification');
    }
}
