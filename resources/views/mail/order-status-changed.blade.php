@php
$statusLabels = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
];
$label = $statusLabels[$order->status] ?? ucfirst($order->status);
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
              <span style="font-size:20px;font-weight:bold;color:#fff;">{{ config('app.name', 'StAutoparts') }}</span>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h1 style="font-size:20px;margin:0 0 8px;">Order Status Update</h1>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Hi {{ $billing['name'] ?? 'there' }},<br>
                Your order <strong>#{{ $order->order_number }}</strong> status is now
                <strong>{{ $label }}</strong>.
              </p>
              @if ($order->status === 'shipped' && $order->tracking_number)
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;background:#fafbfc;margin-bottom:20px;">
                <tr>
                  <td style="padding:14px 16px;font-size:14px;line-height:1.6;">
                    <strong>Tracking number:</strong> {{ $order->tracking_number }}
                    @if ($order->tracking_carrier) <br><strong>Carrier:</strong> {{ $order->tracking_carrier }} @endif
                  </td>
                </tr>
              </table>
              @endif
              <p style="font-size:14px;line-height:1.6;margin:0 0 24px;">
                You can track your order in real time from the
                <a href="{{ route('user.orders.show', $order->id) }}" style="color:#1d3557;">order details</a> page.
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