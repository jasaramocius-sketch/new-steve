@php
$billing = json_decode($order->shipping_details, true) ?? [];
$items = $order->items;
$subtotal = $items->sum(fn ($i) => $i->price * $i->qty);
$shipping = (float) $order->shipping_fee;
$couponDiscount = (float) $order->coupon_discount;
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
              <h1 style="font-size:20px;margin:0 0 8px;">Thank You for Your Order!</h1>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                Hi {{ $billing['name'] ?? 'there' }},<br>
                Your order <strong>#{{ $order->order_number }}</strong> has been placed successfully on
                <strong>{{ $order->created_at->format('d M, Y') }}</strong>.
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;margin-bottom:20px;">
                <tr>
                  <td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Product</td>
                  <td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Qty</td>
                  <td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;text-align:right;">Price</td>
                </tr>
                @foreach ($items as $item)
                <tr>
                  <td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;">{{ $item->product->name ?? 'Product #'.$item->product_id }}</td>
                  <td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;">{{ $item->qty }}</td>
                  <td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;text-align:right;">${{ number_format($item->price * $item->qty, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                  <td colspan="2" style="padding:10px 16px;font-size:13px;">Subtotal</td>
                  <td style="padding:10px 16px;font-size:13px;text-align:right;">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                  <td colspan="2" style="padding:10px 16px;font-size:13px;">Shipping</td>
                  <td style="padding:10px 16px;font-size:13px;text-align:right;">${{ number_format($shipping, 2) }}</td>
                </tr>
                @if ($couponDiscount > 0)
                <tr>
                  <td colspan="2" style="padding:10px 16px;font-size:13px;">Coupon ({{ $order->coupon_code }})</td>
                  <td style="padding:10px 16px;font-size:13px;text-align:right;">-${{ number_format($couponDiscount, 2) }}</td>
                </tr>
                @endif
                <tr>
                  <td colspan="2" style="padding:12px 16px;font-size:14px;font-weight:bold;">Total</td>
                  <td style="padding:12px 16px;font-size:14px;font-weight:bold;text-align:right;">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
              </table>
              <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                <strong>Shipping address:</strong><br>
                {{ $billing['address'] ?? '' }}, {{ $billing['city'] ?? '' }}, {{ $billing['state'] ?? '' }} {{ $billing['zip_code'] ?? '' }}<br>
                {{ $billing['country'] ?? '' }}
              </p>
              <p style="font-size:14px;line-height:1.6;margin:0 0 24px;">
                You can track your order anytime from your <a href="{{ route('user.orders') }}" style="color:#1d3557;">order history</a>.
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