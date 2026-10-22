@php
$subject = $contact->subject ?: 'Your Inquiry';
$isProduct = (bool) $contact->product_id;
@endphp
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="background:#1d3557;padding:24px 32px;">
              <span style="font-size:20px;font-weight:bold;color:#fff;">{{ config('app.name', 'StAutoparts') }}</span>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h1 style="font-size:20px;margin:0 0 8px;">Thanks for getting in touch</h1>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Hi {{ $contact->name }},<br>
                Regarding your {{ $isProduct ? 'product inquiry about "'.$contact->product->name.'"' : 'inquiry' }}:
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;background:#fafbfc;margin-bottom:20px;">
                <tr>
                  <td style="padding:14px 16px;font-size:14px;line-height:1.6;">{{ $contact->reply }}</td>
                </tr>
              </table>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                If you need any further assistance, feel free to reply to this email.
              </p>
              <p style="font-size:14px;line-height:1.6;margin:0;">— {{ config('app.name', 'StAutoparts') }} Team</p>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 32px;background:#f8f9fa;font-size:12px;color:#999;">
              &copy; {{ date('Y') }} {{ config('app.name', 'StAutoparts') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>