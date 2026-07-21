<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to PT Software</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
        .header { background: #0f172a; padding: 24px; text-align: center; color: #ffffff; }
        .header h2 { margin: 0; font-size: 1.5rem; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0 0; color: #94a3b8; font-size: 0.85rem; }
        .content { padding: 30px 24px; line-height: 1.6; }
        .cred-box { background: #f1f5f9; border-left: 4px solid #3b82f6; padding: 18px; border-radius: 6px; margin: 20px 0; }
        .cred-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .cred-val { font-size: 1.1rem; font-weight: bold; color: #0f172a; font-family: monospace; margin-bottom: 10px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 0.75rem; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>PT SOFTWARE PORTAL</h2>
            <p>ISO/IEC 17043 Proficiency Testing Management</p>
        </div>
        <div class="content">
            <h3>Welcome, {{ $lab->contact_person }}!</h3>
            <p>Your laboratory <strong>{{ $lab->laboratory_name }}</strong> has been successfully registered on our ISO/IEC 17043 Proficiency Testing Portal.</p>
            
            <p>Below are your secure account login credentials to participate in upcoming PT Schemes:</p>

            <div class="cred-box">
                <div class="cred-label">Registered Email / Username:</div>
                <div class="cred-val">{{ $lab->email }}</div>

                <div class="cred-label">Auto-Generated System User ID:</div>
                <div class="cred-val">{{ $lab->username }}</div>

                <div class="cred-label">Password:</div>
                <div class="cred-val">{{ $rawPassword }}</div>
            </div>

            <p style="text-align: center;">
                <a href="{{ route('user.login') }}" class="btn">Log In to Participant Portal</a>
            </p>

            <p style="font-size: 0.85rem; color: #64748b; margin-top: 25px;">
                Note: For security reasons, please keep your User ID and password safe. You can log in using either your email address or Auto-Generated User ID ({{ $lab->username }}).
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PT Software. ISO/IEC 17043 Accredited Proficiency Testing Provider.
        </div>
    </div>
</body>
</html>
