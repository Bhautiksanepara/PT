<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $labName;
    public string $couponCode;
    public string $discountType;
    public float  $discountValue;
    public string $discountDisplay;   // e.g. "15% off" or "₹500.00 flat off"
    public string $validity;          // human-readable expiry date
    public string $usagePolicy;       // "One-time use only" or "Multiple use (unlimited)"
    public bool   $isClientSpecific;

    public function __construct(
        string  $labName,
        string  $couponCode,
        string  $discountType,
        float   $discountValue,
        ?string $expiryDate,
        bool    $isOneTimeUse,
        bool    $isClientSpecific
    ) {
        $this->labName          = $labName;
        $this->couponCode       = $couponCode;
        $this->discountType     = $discountType;
        $this->discountValue    = $discountValue;
        $this->isClientSpecific = $isClientSpecific;

        $this->discountDisplay = $discountType === 'percentage'
            ? "{$discountValue}% off"
            : '₹' . number_format($discountValue, 2) . ' flat off';

        $this->validity = $expiryDate
            ? Carbon::parse($expiryDate)->format('d M Y')
            : 'No expiry — valid indefinitely';

        $this->usagePolicy = $isOneTimeUse
            ? 'One-time use only'
            : 'Multiple use (unlimited)';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Exclusive Coupon Code: {$this->couponCode}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.referral_code',
        );
    }
}
