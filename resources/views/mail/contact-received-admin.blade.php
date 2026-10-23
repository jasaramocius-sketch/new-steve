<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New contact form submission</title>
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
            background-color: #dc3545;
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
            margin: 0 0 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .info-table th,
        .info-table td {
            text-align: left;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
        }

        .info-table th {
            background-color: #f8f9fa;
            width: 30%;
        }

        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 16px 20px;
            border-radius: 4px;
            margin: 20px 0;
            white-space: pre-line;
        }

        .btn {
            display: inline-block;
            background-color: #0d6efd;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin-top: 10px;
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
            <h1>New Contact Inquiry</h1>
        </div>
        <div class="content">
            <p>You have received a new message from the contact form.</p>
            <table class="info-table">
                <tr>
                    <th>Name</th>
                    <td>{{ $contact->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $contact->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $contact->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Subject</th>
                    <td>{{ $contact->subject }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $contact->created_at->format('d M, Y h:i A') }}</td>
                </tr>
            </table>
            <p><strong>Message:</strong></p>
            <div class="message-box">{{ $contact->message }}</div>
            <p>
                <a href="{{ route('admin.contacts.show', $contact->id) }}" class="btn">View & Reply in Admin Panel</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'StAutoparts') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
