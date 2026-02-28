<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>NACK – Reset Your Password</title>

    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 650px;
            background: #ffffff;
            margin: 40px auto;
            padding: 35px 45px;
            border-radius: 14px;
            border-top: 6px solid #0071BC;
            /* NACK Blue */
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo img {
            max-height: 60px;
        }

        .title {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #333333;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .content {
            font-size: 16px;
            color: #555555;
            line-height: 1.7;
        }

        .btn {
            display: inline-block;
            margin-top: 28px;
            padding: 14px 32px;
            background-color: #0071BC;
            /* NACK Blue */
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: bold;
        }

        .footer {
            margin-top: 35px;
            font-size: 14px;
            text-align: center;
            color: #888888;
        }

        a {
            color: #0071BC;
            /* NACK Blue */
        }
    </style>

</head>

<body>
    <div class="container">

        <div class="logo">
            <img src="{{ $brand_logo }}" alt="NACK Logo">
        </div>

        <div class="title">Reset Your Password</div>

        <div class="content">
            <p>Hello,</p>

            <p>You requested a password reset for your <strong>NACK</strong> account.
                Click the button below to set a new password:</p>

            <p style="text-align: center;">
                <a href="{{ $url }}" class="btn">Reset Password</a>
            </p>

            <p>If the button doesn’t work, copy and paste this link into your browser:</p>

            <p style="word-break: break-all; color:#0071BC;">
                {{ $url }}
            </p>

            <p>If you didn't request this, please ignore this email.</p>
        </div>

        <div class="footer">
            © {{ date('Y') }} NACK — All rights reserved.
        </div>

    </div>
</body>

</html>