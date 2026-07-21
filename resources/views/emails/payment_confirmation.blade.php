<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f8fafc; padding: 30px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0 0; font-size: 13px; color: #94a3b8; }
        .body-content { padding: 30px; }
        .status-badge { display: inline-block; background-color: #dcfce7; color: #166534; font-weight: bold; font-size: 12px; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 20px; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 12px 15px; text-align: left; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        .details-table th { background-color: #f1f5f9; color: #475569; font-weight: 600; }
        .summary-box { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px 20px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; }
        .summary-total { font-weight: bold; font-size: 16px; color: #1e3a8a; border-top: 1px solid #cbd5e1; padding-top: 8px; margin-top: 8px; }
        .btn-action { display: inline-block; background-color: #2563eb; color: #ffffff !important; font-weight: bold; font-size: 14px; padding: 12px 28px; text-decoration: none; border-radius: 6px; margin-top: 25px; text-align: center; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2>PT SOFTWARE PROVIDER</h2>
                <p>ISO/IEC 17043 Accredited Proficiency Testing Provider</p>
            </div>
            
            <div class="body-content">
                <span class="status-badge">✓ Payment Confirmed</span>

                <p style="font-size: 15px; margin-top: 0;">Dear <strong>{{ $registration->lab->contact_person }}</strong> ({{ $registration->lab->laboratory_name }}),</p>
                <p style="font-size: 14px; color: #475569; line-height: 1.5;">
                    Thank you for your payment! Your registration for the Proficiency Testing scheme has been successfully processed and confirmed.
                </p>

                <table class="details-table">
                    <tr>
                        <th>Registration Number</th>
                        <td><strong style="color: #2563eb;">{{ $registration->registration_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>PT Scheme Code</th>
                        <td>{{ $registration->program->program_code }} — {{ $registration->program->program_name }}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID</th>
                        <td style="font-family: monospace;">{{ $payment->transaction_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td>{{ ucfirst($payment->payment_method ?? 'Stripe Card') }}</td>
                    </tr>
                    <tr>
                        <th>Date of Payment</th>
                        <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>

                <div class="summary-box">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; padding: 4px 0; font-size: 14px; color: #64748b;">Scheme Program Fee:</td>
                            <td style="border: none; padding: 4px 0; font-size: 14px; text-align: right; font-weight: 600;">₹{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @if($payment->discount_amount > 0)
                        <tr>
                            <td style="border: none; padding: 4px 0; font-size: 14px; color: #16a34a;">Referral Discount Applied:</td>
                            <td style="border: none; padding: 4px 0; font-size: 14px; text-align: right; color: #16a34a; font-weight: 600;">- ₹{{ number_format($payment->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="border: none; padding: 8px 0 0 0; font-size: 16px; font-weight: bold; color: #0f172a; border-top: 1px dashed #cbd5e1;">Total Amount Paid:</td>
                            <td style="border: none; padding: 8px 0 0 0; font-size: 16px; font-weight: bold; color: #2563eb; text-align: right; border-top: 1px dashed #cbd5e1;">₹{{ number_format($payment->final_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <div style="text-align: center;">
                    <a href="{{ route('user.invoice', $registration->registration_id) }}" class="btn-action">Download Tax Invoice</a>
                </div>

                <p style="font-size: 13px; color: #64748b; margin-top: 30px; line-height: 1.4;">
                    <strong>Next Steps:</strong> Our logistics team will process sample allocation and dispatch. You can track your sample shipping status and view your Tax Invoice anytime from your <a href="{{ route('user.dashboard') }}" style="color: #2563eb;">Participant Dashboard</a>.
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0 0 5px 0;">This is an automated payment confirmation email from PT Software Provider.</p>
                <p style="margin: 0;">If you have any questions regarding your registration, please contact <a href="mailto:pt@ptsoftware.com" style="color: #2563eb;">pt@ptsoftware.com</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
