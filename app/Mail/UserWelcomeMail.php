<?php

namespace App\Mail;

use App\Models\Lab;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lab;
    public $rawPassword;

    public function __construct(Lab $lab, string $rawPassword)
    {
        $this->lab = $lab;
        $this->rawPassword = $rawPassword;
    }

    public function build()
    {
        return $this->subject('Welcome to PT Software - Your Laboratory Account Credentials')
                    ->view('emails.user_welcome');
    }
}
