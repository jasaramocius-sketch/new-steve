<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoiceSettings['invoice_title'] }} - {{ $invoiceNumber }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        body.compact {
            font-size: 10px;
            padding: 12px;
        }
        .header {
            border-bottom: 2px solid {{ $invoiceSettings['invoice_primary_color'] }};
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header .title {
            font-size: 24px;
            font-weight: 700;
            color: {{ $invoiceSettings['invoice_primary_color'] }};
        }
        .header .order-info {
            font-size: 13px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #f5f5f5;
            border-top: 2px solid {{ $invoiceSettings['invoice_primary_color'] }};
            font-weight: 600;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        table th.right {
            text-align: right;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }
        table td.right {
            text-align: right;
        }
        .totals td {
            border-bottom: none;
            padding: 4px 10px;
        }
        .totals tr.grand-total td {
            border-top: 2px solid #333;
            font-weight: 700;
            font-size: 14px;
        }
        .address-section {
            margin-bottom: 20px;
        }
        .address-section .label {
            font-weight: 600;
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .address-section .value {
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
        .business-details {
            font-size: 11px;
            line-height: 1.5;
            margin-top: 8px;
        }
        .invoice-copy {
            margin: 14px 0;
            line-height: 1.5;
        }
        .section-label {
            font-weight: 600;
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
    </style>
</head>
<body class="{{ $invoiceSettings['invoice_layout'] === 'compact' ? 'compact' : '' }}">

    <div class="header">
        <table style="margin-bottom:0;">
            <tr>
                <td style="width:50%;border:none;">
                    <div class="title">{{ $invoiceSettings['invoice_title'] }}</div>
                    <div class="order-info">Invoice #{{ $invoiceNumber }}</div>
                    <div class="order-info">Date: {{ optional($order->created_at)->format($invoiceSettings['invoice_date_format']) }}</div>
                    @if ($invoiceSettings['invoice_show_business'])
                        <div class="business-details">
                            @if ($invoiceSettings['invoice_business_name']) <strong>{{ $invoiceSettings['invoice_business_name'] }}</strong><br>@endif
                            @if ($invoiceSettings['invoice_business_address']) {!! nl2br(e($invoiceSettings['invoice_business_address'])) !!}<br>@endif
                            @if ($invoiceSettings['invoice_business_phone']) {{ $invoiceSettings['invoice_business_phone'] }}<br>@endif
                            @if ($invoiceSettings['invoice_business_email']) {{ $invoiceSettings['invoice_business_email'] }}<br>@endif
                            @if ($invoiceSettings['invoice_business_tax_id']) Tax / Business ID: {{ $invoiceSettings['invoice_business_tax_id'] }}@endif
                        </div>
                    @endif
                </td>
                <td style="width:50%;text-align:right;border:none;">
                    @if ($invoiceLogo)
                        <img src="{{ $invoiceLogo }}" alt="Logo" style="max-height:{{ $invoiceSettings['invoice_layout'] === 'compact' ? '36px' : '50px' }};">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @php
        $shipping = is_array($order->shipping_details)
            ? $order->shipping_details
            : json_decode($order->shipping_details, true);
        $shippingFee = $order->shipping_fee ?? 0;
        $taxAmount = $order->tax ?? 0;
        $couponDiscount = $order->coupon_discount ?? 0;
        $totalAmount = $order->total_amount ?? 0;
        $subTotal = $totalAmount - $shippingFee - $taxAmount + $couponDiscount;
        $currencyLabel = trim((string) $invoiceSettings['invoice_currency_label']);
        $formatAmount = fn ($amount) => ($currencyLabel !== '' ? $currencyLabel.' ' : '').number_format((float) $amount, 2);
    @endphp

    @if ($invoiceSettings['invoice_show_billing'] || $invoiceSettings['invoice_show_payment'] || $invoiceSettings['invoice_show_delivery'])
        <div class="address-section">
            <table style="margin-bottom:0;">
                <tr>
                    @if ($invoiceSettings['invoice_show_billing'])
                        <td style="width:50%;border:none;">
                            <div class="label">Bill To</div>
                            <div class="value">
                                {{ $shipping['name'] ?? $order->user->name ?? 'N/A' }}<br>
                                {{ $shipping['email'] ?? $order->user->email ?? '' }}<br>
                                {{ $shipping['phone'] ?? '' }}<br>
                                {{ $shipping['address'] ?? '' }}<br>
                                {{ $shipping['city'] ?? '' }}, {{ $shipping['state'] ?? '' }} {{ $shipping['zip_code'] ?? '' }}<br>
                                {{ $shipping['country'] ?? '' }}
                            </div>
                        </td>
                    @endif
                    @if ($invoiceSettings['invoice_show_payment'] || $invoiceSettings['invoice_show_delivery'])
                        <td style="width:50%;border:none;">
                            @if ($invoiceSettings['invoice_show_payment'])
                                <div class="label">Payment Method</div>
                                <div class="value">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}</div>
                            @endif
                            @if ($invoiceSettings['invoice_show_delivery'])
                                <div class="label" style="margin-top:10px;">Delivery Type</div>
                                <div class="value">{{ $order->delivery_type ?? 'Home Delivery' }}</div>
                            @endif
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    @if ($invoiceSettings['invoice_show_items'])
        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:45%;">{{ $invoiceSettings['invoice_label_product'] }}</th>
                    <th style="width:15%;">{{ $invoiceSettings['invoice_label_quantity'] }}</th>
                    <th style="width:15%;" class="right">{{ $invoiceSettings['invoice_label_price'] }}</th>
                    <th style="width:20%;" class="right">{{ $invoiceSettings['invoice_label_line_total'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->product->name ?? 'Product Unavailable' }}</td>
                    <td>{{ $item->quantity ?? $item->qty ?? 1 }}</td>
                    <td class="right">{{ $formatAmount($item->price ?? 0) }}</td>
                    <td class="right">{{ $formatAmount(($item->price ?? 0) * ($item->quantity ?? $item->qty ?? 1)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($invoiceSettings['invoice_show_subtotal'] || $invoiceSettings['invoice_show_shipping'] || $invoiceSettings['invoice_show_tax'] || $invoiceSettings['invoice_show_discount'] || $invoiceSettings['invoice_show_total'])
    <table class="totals" style="width:40%;margin-left:auto;">
        @if ($invoiceSettings['invoice_show_subtotal'])
        <tr>
            <td style="width:50%;">{{ $invoiceSettings['invoice_label_subtotal'] }}</td>
            <td class="right">{{ $formatAmount($subTotal) }}</td>
        </tr>
        @endif
        @if ($invoiceSettings['invoice_show_shipping'])
        <tr>
            <td>{{ $invoiceSettings['invoice_label_shipping'] }}</td>
            <td class="right">{{ $formatAmount($shippingFee) }}</td>
        </tr>
        @endif
        @if ($invoiceSettings['invoice_show_tax'])
        <tr>
            <td>{{ $invoiceSettings['invoice_label_tax'] }}</td>
            <td class="right">{{ $formatAmount($taxAmount) }}</td>
        </tr>
        @endif
        @if($invoiceSettings['invoice_show_discount'] && ($order->coupon_discount ?? 0) > 0)
        <tr>
            <td>{{ $invoiceSettings['invoice_label_discount'] }}</td>
            <td class="right">-{{ $formatAmount($order->coupon_discount) }}</td>
        </tr>
        @endif
        @if ($invoiceSettings['invoice_show_total'])
        <tr class="grand-total">
            <td>{{ $invoiceSettings['invoice_label_total'] }}</td>
            <td class="right">{{ $formatAmount($totalAmount) }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if ($invoiceSettings['invoice_note'])
        <div class="invoice-copy"><div class="section-label">Note</div>{!! nl2br(e($invoiceSettings['invoice_note'])) !!}</div>
    @endif
    @if ($invoiceSettings['invoice_terms'])
        <div class="invoice-copy"><div class="section-label">Terms and Conditions</div>{!! nl2br(e($invoiceSettings['invoice_terms'])) !!}</div>
    @endif
    @if ($invoiceSettings['invoice_footer'])
        <div class="footer">{{ $invoiceSettings['invoice_footer'] }}</div>
    @endif

</body>
</html>
