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
              <h1 style="font-size:20px;margin:0 0 8px;">Reset Your Password</h1>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Hi there,<br>
                We received a request to reset the password for the account registered with
                <strong>{{ $email }}</strong>. Click the button below to choose a new password.
              </p>
              <a href="{{ $url }}"
                 style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">Reset Password</a>
              <p style="font-size:13px;line-height:1.6;margin:20px 0 0;color:#999;">
                This password reset link is valid for {{ $count }} minutes.
                If you did not request a password reset, no further action is needed.
              </p>
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