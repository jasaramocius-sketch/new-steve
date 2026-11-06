@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-invoice-settings-page', 'pageClass' => 'admin-invoice-settings-page'])
@section('page-title', 'Invoice Settings')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Invoice Settings - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="mb-1">Invoice Settings</h4>
            <p class="text-muted mb-0">Manage the details, appearance, content, and visible sections of order invoice PDFs.</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> All Settings
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('admin.settings.invoice.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <h5 class="mb-3">Company Information</h5>
                        <div class="form-group mb-3">
                            <label class="form-label" for="invoice-logo"><strong>Invoice Logo</strong></label>
                            @if ($invoiceLogoUrl)
                                <div class="mb-3">
                                    <img src="{{ $invoiceLogoUrl }}" alt="Current invoice logo" class="img-fluid rounded border p-2" style="max-height: 120px;">
                                </div>
                            @else
                                <p class="text-muted">No invoice logo is currently set.</p>
                            @endif
                            <input id="invoice-logo" type="file" name="invoice_logo" accept="image/jpeg,image/png" class="form-control @error('invoice_logo') is-invalid @enderror">
                            @error('invoice_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">Upload a JPG or PNG image up to 2 MB. Uploading a new image replaces the current logo.</small>
                            @if ($invoiceLogoUrl)
                                <input type="hidden" name="remove_invoice_logo" value="0">
                                <div class="form-check mt-2">
                                    <input id="remove-invoice-logo" type="checkbox" name="remove_invoice_logo" value="1" class="form-check-input">
                                    <label class="form-check-label" for="remove-invoice-logo">Remove invoice logo</label>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_business_name">Business name</label>
                                <input id="invoice_business_name" name="invoice_business_name" class="form-control" value="{{ old('invoice_business_name', $invoiceSettings['invoice_business_name']) }}">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_business_tax_id">Tax / Business ID</label>
                                <input id="invoice_business_tax_id" name="invoice_business_tax_id" class="form-control" value="{{ old('invoice_business_tax_id', $invoiceSettings['invoice_business_tax_id']) }}">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_business_phone">Business phone</label>
                                <input id="invoice_business_phone" name="invoice_business_phone" class="form-control" value="{{ old('invoice_business_phone', $invoiceSettings['invoice_business_phone']) }}">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_business_email">Business email</label>
                                <input id="invoice_business_email" type="email" name="invoice_business_email" class="form-control" value="{{ old('invoice_business_email', $invoiceSettings['invoice_business_email']) }}">
                            </div>
                            <div class="col-12 form-group mb-4">
                                <label for="invoice_business_address">Business address</label>
                                <textarea id="invoice_business_address" name="invoice_business_address" rows="2" class="form-control">{{ old('invoice_business_address', $invoiceSettings['invoice_business_address']) }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">Branding and Layout</h5>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_primary_color">Primary color</label>
                                <input id="invoice_primary_color" type="color" name="invoice_primary_color" class="form-control form-control-color" value="{{ old('invoice_primary_color', $invoiceSettings['invoice_primary_color']) }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_layout">Layout</label>
                                <select id="invoice_layout" name="invoice_layout" class="form-control">
                                    <option value="standard" @selected(old('invoice_layout', $invoiceSettings['invoice_layout']) === 'standard')>Standard</option>
                                    <option value="compact" @selected(old('invoice_layout', $invoiceSettings['invoice_layout']) === 'compact')>Compact</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">Invoice Number and Text</h5>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_prefix">Invoice number prefix</label>
                                <input id="invoice_prefix" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $invoiceSettings['invoice_prefix']) }}" maxlength="30">
                                <small class="form-text text-muted">Displayed before the order number; e.g. INV-1001. Changing the prefix does not change order records.</small>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_date_format">Date format</label>
                                <select id="invoice_date_format" name="invoice_date_format" class="form-control">
                                    @foreach (['d M Y' => '06 Nov 2026', 'M d Y' => 'Nov 06 2026', 'Y-m-d' => '2026-11-06', 'd/m/Y' => '06/11/2026'] as $format => $example)
                                        <option value="{{ $format }}" @selected(old('invoice_date_format', $invoiceSettings['invoice_date_format']) === $format)>{{ $example }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 form-group mb-3">
                                <label for="invoice_title">Invoice heading</label>
                                <input id="invoice_title" name="invoice_title" class="form-control" value="{{ old('invoice_title', $invoiceSettings['invoice_title']) }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_note">Invoice note</label>
                                <textarea id="invoice_note" name="invoice_note" rows="3" maxlength="2000" class="form-control">{{ old('invoice_note', $invoiceSettings['invoice_note']) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="invoice_terms">Terms and conditions</label>
                                <textarea id="invoice_terms" name="invoice_terms" rows="3" maxlength="2000" class="form-control">{{ old('invoice_terms', $invoiceSettings['invoice_terms']) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="invoice_footer">Footer message</label>
                                <textarea id="invoice_footer" name="invoice_footer" rows="2" maxlength="500" class="form-control">{{ old('invoice_footer', $invoiceSettings['invoice_footer']) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label for="invoice_currency_label">Currency label (optional)</label>
                                <input id="invoice_currency_label" name="invoice_currency_label" class="form-control" value="{{ old('invoice_currency_label', $invoiceSettings['invoice_currency_label']) }}" maxlength="10" placeholder="e.g. USD">
                                <small class="form-text text-muted">Shown beside invoice amounts; this label does not convert or change stored order amounts.</small>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">Invoice Labels</h5>
                        <div class="row">
                            @foreach ([
                                'invoice_label_product' => 'Product column',
                                'invoice_label_quantity' => 'Quantity column',
                                'invoice_label_price' => 'Price column',
                                'invoice_label_line_total' => 'Item total column',
                                'invoice_label_subtotal' => 'Subtotal',
                                'invoice_label_shipping' => 'Shipping',
                                'invoice_label_tax' => 'Tax',
                                'invoice_label_discount' => 'Discount',
                                'invoice_label_total' => 'Grand total',
                            ] as $key => $label)
                                <div class="col-md-4 form-group mb-3">
                                    <label for="{{ $key }}">{{ $label }}</label>
                                    <input id="{{ $key }}" name="{{ $key }}" class="form-control" value="{{ old($key, $invoiceSettings[$key]) }}" required>
                                </div>
                            @endforeach
                        </div>

                        <hr>
                        <h5 class="mb-3">Visible Sections</h5>
                        <div class="row">
                            @foreach ([
                                'invoice_show_business' => 'Business details',
                                'invoice_show_billing' => 'Billing address',
                                'invoice_show_payment' => 'Payment method',
                                'invoice_show_delivery' => 'Delivery type',
                                'invoice_show_items' => 'Product items',
                                'invoice_show_subtotal' => 'Subtotal',
                                'invoice_show_shipping' => 'Shipping amount',
                                'invoice_show_tax' => 'Tax amount',
                                'invoice_show_discount' => 'Discount amount',
                                'invoice_show_total' => 'Grand total',
                            ] as $key => $label)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <div class="form-check">
                                        <input id="{{ $key }}" type="checkbox" name="{{ $key }}" value="1" class="form-check-input" @checked(old($key, $invoiceSettings[$key]))>
                                        <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary steve-btn">
                            <i class="fas fa-save me-1"></i> Save Invoice Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-circle-info me-1"></i> Where this appears</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">These settings control the details and appearance of invoice PDFs downloaded from an admin order page. Invoice amounts and customer/order records remain unchanged.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
