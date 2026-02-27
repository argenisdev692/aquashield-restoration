<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Your OTP Verification Code</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #F5F5F6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(12, 35, 64, 0.08);
        }

        /* ── Header with brand gradient ── */
        .header {
            background: linear-gradient(135deg, #0C2340 0%, #1a3a5c 100%);
            padding: 32px 30px;
            text-align: center;
        }

        .header img {
            width: 60px;
            height: auto;
            margin-bottom: 12px;
        }

        .header h1 {
            color: #FFFFFF;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            color: #E6E7E8;
            font-size: 12px;
            margin-top: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .body-content {
            padding: 30px;
            line-height: 1.6;
            color: #0C2340;
        }

        .otp-box {
            background: linear-gradient(135deg, #0C2340 0%, #1a3a5c 100%);
            padding: 30px;
            border-radius: 12px;
            margin: 25px 0;
            text-align: center;
        }

        .otp-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #E6E7E8;
            margin-bottom: 10px;
        }

        .otp-code {
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 14px;
            color: #00B5E2;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            padding: 10px 0;
        }

        .otp-expiry {
            font-size: 12px;
            color: #7a8a9a;
            margin-top: 10px;
        }

        .warning-box {
            background: #FFF7ED;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
            font-size: 13px;
            color: #92400E;
        }

        .footer {
            background: #0C2340;
            padding: 24px 30px;
            text-align: center;
            color: #E6E7E8;
        }

        .footer a {
            color: #00B5E2;
            text-decoration: none;
        }

        .footer .social-links {
            margin-bottom: 16px;
        }

        .footer .social-links a {
            margin: 0 8px;
            display: inline-block;
        }

        .footer .social-links img {
            width: 24px;
            height: 24px;
            opacity: 0.8;
        }

        .footer .company-info {
            font-size: 12px;
            line-height: 1.8;
            color: #7a8a9a;
        }

        .footer .copyright {
            font-size: 11px;
            color: #4a5a6a;
            margin-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 16px;
        }

        .divider {
            height: 3px;
            background: linear-gradient(90deg, #00B5E2 0%, #0091b5 100%);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <img src="{{ asset('img/Logo PNG.png') }}" alt="AquaShield Logo">
            <h1>AquaShield</h1>
            <div class="subtitle">Customer Relationship Management</div>
        </div>

        <div class="divider"></div>

        <div class="body-content">
            <h2 style="color: #0C2340; margin-top: 0; font-size: 22px;">
                Verification Code 🔐
            </h2>

            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            <p>Use the following one-time password (OTP) to verify your identity:</p>

            <div class="otp-box">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">This code expires in {{ $expiresInMinutes ?? 10 }} minutes</div>
            </div>

            <div class="warning-box">
                <strong>⚠️ Security Notice:</strong> Never share this code with anyone. AquaShield staff will never ask
                for your OTP.
            </div>

            <p style="color: #3a5068; font-size: 14px;">If you did not request this code, you can safely ignore this
                email. Someone may have entered your email address by mistake.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="social-links">
                <a href="https://facebook.com/aquashieldcrm">
                    <img src="https://cdn-icons-png.flaticon.com/512/124/124010.png" alt="Facebook">
                </a>
                <a href="https://instagram.com/aquashieldcrm">
                    <img src="https://cdn-icons-png.flaticon.com/512/174/174855.png" alt="Instagram">
                </a>
                <a href="https://twitter.com/aquashieldcrm">
                    <img src="https://cdn-icons-png.flaticon.com/512/124/124021.png" alt="Twitter">
                </a>
                <a href="https://linkedin.com/company/aquashieldcrm">
                    <img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" alt="LinkedIn">
                </a>
            </div>

            <div class="company-info">
                <p style="margin: 3px 0;"><strong>Address:</strong> {{ $companyData->address }}</p>
                <p style="margin: 3px 0;"><strong>Phone:</strong>
                    {{ \App\Helpers\PhoneHelper::format($companyData->phone) }}</p>
                <p style="margin: 3px 0;"><strong>Email:</strong> <a
                        href="mailto:{{ $companyData->email }}">{{ $companyData->email }}</a></p>
                <p style="margin: 3px 0;"><strong>Hours:</strong> Monday–Friday: 9:00 AM – 5:00 PM</p>
            </div>

            <div class="copyright">
                © {{ date('Y') }} {{ $companyData->company_name }}. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>