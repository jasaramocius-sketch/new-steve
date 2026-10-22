@php
$billing = json_decode($order->shipping_details, true) ?? [];
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
              <span style="font-size:20px;font-weight:bold;color:#fff;">{{ config('app.name', 'StAutoparts') }} — Invoice</span>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h1 style="font-size:20px;margin:0 0 8px;">Invoice for Order #{{ $order->order_number }}</h1>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Hi {{ $billing['name'] ?? 'there' }},<br>
                Please find your invoice for order
                <strong>#{{ $order->order_number }}</strong> ({{ $order->created_at->format('d M, Y') }})
                attached to this email as a PDF.
              </p>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Total: <strong>${{ number_format($order->total_amount, 2) }}</strong><br>
                Payment status: <strong>{{ ucfirst($order->payment_status) }}</strong>
              </p>
              <a href="{{ route('user.orders.show', $order->id) }}"
                 style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
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