<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Coupon Code: {{ $couponCode }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .email-header { background: #0f172a; color: #ffffff; padding: 25px 30px; text-align: center; }
        .email-header h2 { margin: 0; font-size: 22px; font-weight: 700; }
        .email-header p { margin: 5px 0 0 0; font-size: 13px; color: #94a3b8; }
        .email-body { padding: 30px; }
        /* Coupon code box */
        .coupon-box { text-align: center; margin: 24px 0; }
        .coupon-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600; margin-bottom: 10px; }
        .coupon-code { display: inline-block; background: #f0fdf4; border: 2px dashed #22c55e; border-radius: 8px; padding: 14px 32px; font-family: monospace; font-size: 28px; font-weight: 800; color: #15803d; letter-spacing: 4px; }
        .discount-tag { display: inline-block; margin-top: 10px; background: #dcfce7; color: #166534; padding: 4px 14px; border-radius: 20px; font-size: 14px; font-weight: 700; }
        /* Info table */
        .info-box { background: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px 20px; border-radius: 4px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .info-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-val { color: #0f172a; font-weight: 700; text-align: right; }
        /* Notice */
        .client-notice { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px 16px; margin: 16px 0; font-size: 13px; color: #92400e; }
        .email-footer { background: #f1f5f9; text-align: center; padding: 15px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .cta-btn { display: inline-block; margin-top: 20px; background: #2563eb; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; }
    </style>
</head>
<body>
    <div class="email-card">
        <!-- Header -->
        <div class="email-header">
            <h2>Proficiency Testing Scheme</h2>
            <p>Exclusive Coupon Code Notification</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p>Dear <strong>{{ $labName }}</strong>,</p>

            @if($isClientSpecific)
                <p>We are delighted to share an <strong>exclusive coupon code</strong> reserved specifically for your laboratory. Use it during your next PT program registration to avail the discount.</p>
            @else
                <p>We are pleased to share a <strong>special coupon code</strong> available to all participating laboratories. Apply it during PT program registration to avail the discount.</p>
            @endif

            <!-- Coupon Code Highlight -->
            <div class="coupon-box">
                <div class="coupon-label">Your Coupon Code</div>
                <div class="coupon-code">{{ $couponCode }}</div>
                <div class="discount-tag">{{ $discountDisplay }}</div>
            </div>

            <!-- Details Table -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Coupon Code:</span>
                    <span class="info-val" style="font-family:monospace; color:#15803d;">{{ $couponCode }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Discount:</span>
                    <span class="info-val" style="color:#16a34a;">{{ $discountDisplay }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Valid Until:</span>
                    <span class="info-val">{{ $validity }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Usage Policy:</span>
                    <span class="info-val">{{ $usagePolicy }}</span>
                </div>
                @if($isClientSpecific)
                <div class="info-row">
                    <span class="info-label">Scope:</span>
                    <span class="info-val" style="color:#dc2626;">Your Laboratory Only</span>
                </div>
                @else
                <div class="info-row">
                    <span class="info-label">Scope:</span>
                    <span class="info-val" style="color:#2563eb;">All Participating Laboratories</span>
                </div>
                @endif
            </div>

            @if($isClientSpecific)
            <div class="client-notice">
                ⚠️ This coupon is exclusively assigned to your laboratory. It cannot be shared with or used by other laboratories.
            </div>
            @endif

            <p style="font-size: 13px; color: #475569;">
                To apply this coupon, enter the code <strong style="font-family:monospace;">{{ $couponCode }}</strong> in the coupon/referral code field during PT program registration checkout.
            </p>

            <div style="text-align:center;">
                <a href="{{ url('/') }}" class="cta-btn">Register for a PT Program</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin:0;">ISO/IEC 17043 Accredited Proficiency Testing Provider</p>
            <p style="margin:4px 0 0 0;">This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
