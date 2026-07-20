<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sample Item Dispatched</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .email-header { background: #0f172a; color: #ffffff; padding: 25px 30px; text-align: center; }
        .email-header h2 { margin: 0; font-size: 22px; font-weight: 700; }
        .email-header p { margin: 5px 0 0 0; font-size: 13px; color: #94a3b8; }
        .email-body { padding: 30px; }
        .info-box { background: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px 20px; border-radius: 4px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-val { color: #0f172a; font-weight: 700; }
        .qr-section { text-align: center; background: #ffffff; border: 2px dashed #cbd5e1; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .qr-code-img { width: 140px; height: 140px; margin-bottom: 10px; border: 1px solid #e2e8f0; padding: 5px; background: #fff; }
        .qr-caption { font-family: monospace; font-size: 13px; font-weight: 700; color: #1e293b; background: #f1f5f9; padding: 4px 12px; border-radius: 4px; display: inline-block; }
        .email-footer { background: #f1f5f9; text-align: center; padding: 15px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="email-header">
            <h2>Proficiency Testing Scheme</h2>
            <p>Official Sample Dispatch Notification</p>
        </div>
        <div class="email-body">
            <p>Dear <strong>{{ $labName }}</strong>,</p>
            <p>We are pleased to inform you that your Proficiency Testing sample item has been dispatched and is en route to your laboratory.</p>

            <!-- QR Code Section -->
            @php
                $qrPayload = "SAMPLE ID: {$sampleCode}\nPROGRAM: {$programCode}\nCOURIER: {$courierName}\nTRACKING #: {$trackingNumber}\nDISPATCH DATE: {$dispatchDate}\nLABORATORY: {$labName}";
            @endphp
            <div class="qr-section">
                <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: 600; color: #475569;">OFFICIAL SAMPLE IDENTIFIER & DISPATCH QR CODE</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrPayload) }}" alt="Sample QR Code" class="qr-code-img">
                <br>
                <span class="qr-caption">Sample ID: {{ $sampleCode }}</span>
                <p style="margin: 8px 0 0 0; font-size: 11px; color: #64748b;">(Scan with any smartphone camera or barcode scanner to read full dispatch payload)</p>
            </div>

            <!-- Shipment Details -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Sample Code:</span>
                    <span class="info-val" style="color:#2563eb;">{{ $sampleCode }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">PT Program:</span>
                    <span class="info-val">{{ $programCode }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Courier Partner:</span>
                    <span class="info-val">{{ $courierName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tracking Number:</span>
                    <span class="info-val" style="font-family:monospace; background:#e2e8f0; padding:2px 6px; border-radius:3px;">{{ $trackingNumber }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dispatch Date:</span>
                    <span class="info-val">{{ $dispatchDate }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569;">Please scan the QR code upon package arrival to confirm sample receipt in your laboratory portal.</p>
        </div>
        <div class="email-footer">
            <p style="margin:0;">ISO/IEC 17043 Accredited Proficiency Testing Provider</p>
        </div>
    </div>
</body>
</html>
