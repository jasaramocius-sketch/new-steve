@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-email-template-edit-page', 'pageClass' => 'admin-email-template-edit-page'])
@section('page-title', 'Edit Email Template')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Edit Email Template'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- <div class="mb-3">
        <a href="{{ route('admin.settings.email-templates') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back to Templates</a>
    </div> -->

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ $template->name }}</h5>
                    <small class="text-muted">{{ $template->description }}</small>
                </div>
                <form action="{{ route('admin.settings.email-templates.update', $template->key) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Email Body (HTML)</label>
                            <textarea name="body_html" rows="20" class="form-control textarea-control" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px;" required>{{ old('body_html', $template->body_html) }}</textarea>
                            <small class="text-muted">Use placeholders like <code>{{ chr(123).chr(123) . 'customer_name' . chr(125).chr(125) }}</code>. HTML is allowed; Blade/PHP is not executed.</small>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check form-switch d-flex align-items-center gap-2">
                                <input type="hidden" name="is_enabled" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_enabled" name="is_enabled" value="1" @checked(old('is_enabled', $template->is_enabled))>
                                <label class="form-check-label" for="is_enabled">Enabled</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Template</button>
                        <button type="button" class="btn btn-outline-secondary" id="preview-email-template"><i class="fas fa-eye"></i> Preview</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Placeholders</h6></div>
                <div class="card-body">
                    <p class="text-muted small">Use these placeholders in subject or body. They will be replaced with real values when email is sent.</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(($template->placeholders ?? []) as $ph)
                            <code>{{ chr(123).chr(123) . $ph . chr(125).chr(125) }}</code>
                        @endforeach
                        <code>{{ chr(123).chr(123) . 'app_name' . chr(125).chr(125) }}</code>
                        <code>{{ chr(123).chr(123) . 'year' . chr(125).chr(125) }}</code>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Reset to Default</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Restore the original subject and body for this template.</p>
                    <form action="{{ route('admin.settings.email-templates.reset', $template->key) }}" method="POST" onsubmit="return confirm('Reset this template to default? Your current changes will be lost.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-undo"></i> Reset to Default</button>
                    </form>
                </div>
            </div>

            @if($default)
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Default Subject</h6></div>
                <div class="card-body">
                    <code>{{ $default['subject'] }}</code>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="emailTemplatePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Preview: {{ $template->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background:#f4f6f8;">
                <iframe id="emailTemplatePreviewFrame" title="Email Template Preview" style="width:100%; min-height:70vh; border:0; background:#f4f6f8;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('preview-email-template');
    var textarea = document.querySelector('textarea[name="body_html"]');
    var subjectInput = document.querySelector('input[name="subject"]');
    var frame = document.getElementById('emailTemplatePreviewFrame');
    var modalEl = document.getElementById('emailTemplatePreviewModal');
    if (!btn || !textarea || !frame || !modalEl || typeof bootstrap === 'undefined') return;

    var samples = {
        customer_name: 'John Doe',
        order_number: 'ORD123456789',
        order_date: '26 Nov, 2026',
        order_total: '$120.00',
        payment_method: 'Card',
        shipping_address: '123 Main St, New York, NY 10001, USA',
        order_items_html: '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;margin-bottom:20px;"><tr><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Product</td><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Qty</td><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;text-align:right;">Price</td></tr><tr><td style="padding:10px 16px;font-size:13px;">Sample Product</td><td style="padding:10px 16px;font-size:13px;">1</td><td style="padding:10px 16px;font-size:13px;text-align:right;">$120.00</td></tr></table>',
        order_url: '#',
        tracking_number: 'TRK123456789',
        tracking_carrier: 'FedEx',
        tracking_url: '#',
        tracking_url_block: '<p style="font-size:14px;line-height:1.6;margin:0 0 20px;"><a href="#" style="color:#1d3557;">Track your shipment</a></p>',
        refund_amount: '$120.00',
        refund_method: 'Card',
        refund_reference: 're_123456789',
        return_status: 'Approved',
        return_reason: 'Damaged item',
        admin_note: 'Your return has been approved.',
        admin_note_block: '<p style="font-size:14px;line-height:1.6;margin:0 0 20px;"><strong>Admin note:</strong><br>Your return has been approved.</p>',
        reset_url: '#',
        app_name: @json(config('app.name', 'Steve Autoparts')),
        year: @json(date('Y'))
    };

    function applySamples(str) {
        return String(str || '').replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, function (m, key) {
            return Object.prototype.hasOwnProperty.call(samples, key) ? String(samples[key]) : m;
        });
    }

    btn.addEventListener('click', function () {
        var html = applySamples(textarea.value);
        var subject = applySamples(subjectInput ? subjectInput.value : '');
        var isFullDoc = /<!doctype html|<html[\s>]/i.test(html);
        var doc = isFullDoc ? html : ('<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' +
            subject.replace(/[<>&"]/g, function (c) { return ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]); }) +
            '</title></head><body style="margin:0;padding:0;background:#f4f6f8;">' + html + '</body></html>');
        frame.srcdoc = doc;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });
});
</script>
@endsection
