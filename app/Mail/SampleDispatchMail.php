<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SampleDispatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public $labName;
    public $sampleCode;
    public $programCode;
    public $courierName;
    public $trackingNumber;
    public $dispatchDate;
    public $qrCode;

    public function __construct($labName, $sampleCode, $programCode, $courierName, $trackingNumber, $dispatchDate, $qrCode = null)
    {
        $this->labName = $labName;
        $this->sampleCode = $sampleCode;
        $this->programCode = $programCode;
        $this->courierName = $courierName;
        $this->trackingNumber = $trackingNumber;
        $this->dispatchDate = $dispatchDate;
        $this->qrCode = $qrCode ?? ('QR-' . $sampleCode);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Sample Dispatched: {$this->sampleCode} ({$this->programCode})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sample_dispatch',
        );
    }
}
