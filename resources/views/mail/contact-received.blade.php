<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We received your message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #0d6efd;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px 20px;
            color: #333333;
            line-height: 1.6;
        }

        .content p {
            margin: 0 0 16px;
        }

        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 16px 20px;
            border-radius: 4px;
            margin: 20px 0;
            white-space: pre-line;
        }

        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'StAutoparts') }}</h1>
        </div>
        <div class="content">
            <p>Hi {{ $contact->name }},</p>
            <p>Thank you for reaching out to us. We have received your message and our team will get back to you as soon as possible.</p>
            <p><strong>Subject:</strong> {{ $contact->subject }}</p>
            <div class="message-box">{{ $contact->message }}</div>
            <p>If you have any urgent questions, please reply to this email.</p>
            <p>Best regards,<br>{{ config('app.name', 'StAutoparts') }} Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'StAutoparts') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
