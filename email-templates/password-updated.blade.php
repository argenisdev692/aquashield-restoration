<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Update Confirmation</title>
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

        .success-box {
            background: linear-gradient(135deg, #0C2340 0%, #1a3a5c 100%);
            padding: 28px;
            border-radius: 12px;
            margin: 25px 0;
            text-align: center;
        }

        .success-icon {
            font-size: 42px;
            margin-bottom: 8px;
        }

        .success-text {
            font-size: 18px;
            font-weight: 700;
            color: #22c55e;
            margin-bottom: 4px;
        }

        .success-sub {
            font-size: 13px;
            color: #E6E7E8;
        }

        .info-box {
            background: rgba(0, 181, 226, 0.06);
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #00B5E2;
        }

        .info-box p {
            margin: 4px 0;
            font-size: 13px;
            color: #0C2340;
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
                Password Updated Successfully ✅
            </h2>

            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            <p>Your password has been successfully updated on <strong>{{ date('F j, Y') }}</strong> at
                <strong>{{ date('g:i A') }}</strong>.</p>

            <div class="success-box">
                <div class="success-icon">🔒</div>
                <div class="success-text">Password Changed</div>
                <div class="success-sub">Your account is now secured with the new password</div>
            </div>

            <div class="info-box">
                <p><strong>Account Details:</strong></p>
                <p><strong>Username:</strong> {{ $user->username }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
            </div>

            <div class="warning-box">
                <strong>⚠️ Wasn't you?</strong> If you did not change your password, please contact our support team
                immediately and secure your account.
            </div>

            <p style="font-size: 14px; color: #3a5068;">For added security, we recommend:</p>
            <ul style="color: #3a5068; font-size: 14px; padding-left: 20px;">
                <li>Using a unique password that you don't use on other websites</li>
                <li>Enabling two-factor authentication (2FA)</li>
                <li>Not sharing your credentials with anyone</li>
            </ul>
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