<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Suspicious Login Activity</title>
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

        .alert-banner {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
        }

        .alert-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .alert-title {
            font-size: 18px;
            font-weight: 800;
            color: #FFFFFF;
            margin: 0;
        }

        .alert-sub {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 4px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .details-table td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid rgba(12, 35, 64, 0.06);
        }

        .details-table .label {
            font-weight: 700;
            color: #0C2340;
            width: 120px;
            white-space: nowrap;
        }

        .details-table .value {
            color: #3a5068;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 12px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #00B5E2 0%, #0091b5 100%);
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .warning-box {
            background: #FEF2F2;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
            font-size: 13px;
            color: #991B1B;
        }

        .steps-list {
            background: rgba(0, 181, 226, 0.05);
            border-radius: 8px;
            padding: 16px 20px;
            margin: 16px 0;
            border-left: 4px solid #00B5E2;
        }

        .steps-list ol {
            margin: 0;
            padding-left: 18px;
            color: #0C2340;
            font-size: 13px;
        }

        .steps-list ol li {
            margin-bottom: 6px;
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
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <img src="{{ asset('img/Logo PNG.png') }}" alt="AquaShield Logo">
            <h1>AquaShield</h1>
            <div class="subtitle">Security Alert</div>
        </div>

        <div class="divider"></div>

        <div class="body-content">
            <!-- Alert Banner -->
            <div class="alert-banner">
                <div class="alert-icon">🚨</div>
                <div class="alert-title">Suspicious Login Activity</div>
                <div class="alert-sub">Multiple failed login attempts detected</div>
            </div>

            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            <p>We detected <strong>multiple failed login attempts</strong> on your AquaShield account. This may indicate
                that someone is trying to access your account without authorization.</p>

            <!-- Attempt Details -->
            <table class="details-table">
                <tr>
                    <td class="label">🌐 IP Address</td>
                    <td class="value">{{ $ipAddress }}</td>
                </tr>
                <tr>
                    <td class="label">🖥️ Browser</td>
                    <td class="value">{{ $browser }}</td>
                </tr>
                <tr>
                    <td class="label">🕐 Time</td>
                    <td class="value">{{ $attemptedAt }}</td>
                </tr>
                <tr>
                    <td class="label">📍 Target</td>
                    <td class="value">{{ $route }}</td>
                </tr>
            </table>

            <!-- Warning -->
            <div class="warning-box">
                <strong>⚠️ If this was NOT you:</strong> Your account may be at risk. Someone may be trying to guess
                your password. We strongly recommend taking immediate action.
            </div>

            <!-- Recommended Steps -->
            <div class="steps-list">
                <p style="margin: 0 0 8px; font-weight: 700; font-size: 13px;">Recommended Security Steps:</p>
                <ol>
                    <li>Change your password immediately</li>
                    <li>Enable two-factor authentication (2FA)</li>
                    <li>Review your recent account activity</li>
                    <li>Contact our support team if unauthorized access occurred</li>
                </ol>
            </div>

            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ url('/login') }}" class="cta-button">Secure My Account</a>
            </div>

            <p style="font-size: 13px; color: #6a7a8a;">
                If this was you, no action is needed. Your account has been temporarily rate-limited for protection —
                please wait a few minutes before trying again.
            </p>
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
                @if(isset($companyData))
                    <p style="margin: 3px 0;"><strong>Address:</strong> {{ $companyData->address }}</p>
                    <p style="margin: 3px 0;"><strong>Phone:</strong>
                        {{ \App\Helpers\PhoneHelper::format($companyData->phone) }}</p>
                    <p style="margin: 3px 0;"><strong>Email:</strong> <a
                            href="mailto:{{ $companyData->email }}">{{ $companyData->email }}</a></p>
                @endif
                <p style="margin: 3px 0;"><strong>Hours:</strong> Monday–Friday: 9:00 AM – 5:00 PM</p>
            </div>

            <div class="copyright">
                © {{ date('Y') }} AquaShield CRM. All rights reserved.
            </div>
        </div>
    </div>
</body>

</html>