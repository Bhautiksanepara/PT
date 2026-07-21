<?php

namespace App\Mail;

use App\Models\ProgramRegistration;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $payment;

    public function __construct(ProgramRegistration $registration, Payment $payment)
    {
        $this->registration = $registration;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject("Payment Confirmation & Invoice - Registration #{$this->registration->registration_number}")
                    ->view('emails.payment_confirmation');
    }
}
