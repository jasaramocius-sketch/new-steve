@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-api-integrations-create-page', 'pageClass' => 'admin-api-integrations-create-page'])
@section('page-title', 'Add API Integration')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add API Integration - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Add New API Integration</h4>
        <p class="text-muted mb-0 small">Configure courier, payment, SMS, WhatsApp or any custom API</p>
    </div>
    <a href="{{ route('admin.api-integrations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<form action="{{ route('admin.api-integrations.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Basic Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Integration Type <span class="text-danger">*</span></label>
                            <select name="type" id="integration_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="courier">Courier / Shipping</option>
                                <option value="payment">Payment Gateway</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Provider <span class="text-danger">*</span></label>
                            <select name="provider" id="integration_provider" class="form-select" required>
                                <option value="">Select Type first</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Shiprocket Live" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Auth Type <span class="text-danger">*</span></label>
                            <select name="auth_type" class="form-select" required>
                                <option value="bearer">Bearer Token</option>
                                <option value="api_key">API Key</option>
                                <option value="basic">Basic Auth</option>
                                <option value="oauth">OAuth</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Base URL</label>
                            <input type="url" name="base_url" class="form-control" placeholder="https://api.example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control textarea-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" id="credentials_card" style="display:none;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">API Credentials</h5>
                </div>
                <div class="card-body" id="credentials_fields"></div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Endpoints (Optional)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Create Shipment</label>
                            <input type="text" name="endpoints[create_shipment]" class="form-control" placeholder="/v1/shipments/create">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Track Order</label>
                            <input type="text" name="endpoints[track]" class="form-control" placeholder="/v1/track">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cancel Shipment</label>
                            <input type="text" name="endpoints[cancel]" class="form-control" placeholder="/v1/shipments/cancel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Get Couriers</label>
                            <input type="text" name="endpoints[couriers]" class="form-control" placeholder="/v1/couriers">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1">
                        <label class="form-check-label" for="is_active">Activate Integration</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1">
                        <label class="form-check-label" for="is_default">Set as Default</label>
                        <small class="d-block text-muted">Only one default per type</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="0" min="0">
                        <small class="text-muted">Higher priority = preferred</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 steve-btn"><i class="fas fa-save me-1"></i> Save Integration</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const providers = @json($providers);
const typeSelect = document.getElementById('integration_type');
const providerSelect = document.getElementById('integration_provider');
const credsCard = document.getElementById('credentials_card');
const credsFields = document.getElementById('credentials_fields');

typeSelect.addEventListener('change', function() {
    const type = this.value;
    providerSelect.innerHTML = '<option value="">Select Provider</option>';
    if (providers[type]) {
        Object.keys(providers[type]).forEach(key => {
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = providers[type][key].name;
            providerSelect.appendChild(opt);
        });
    }
});

providerSelect.addEventListener('change', function() {
    const type = typeSelect.value;
    const provider = this.value;
    if (!type || !provider) { credsCard.style.display = 'none'; return; }
    fetch('{{ route('admin.api-integrations.get-provider-fields') }}?type=' + type + '&provider=' + provider)
        .then(r => r.json())
        .then(data => {
            credsFields.innerHTML = '';
            if (data.fields && data.fields.length) {
                credsCard.style.display = 'block';
                data.fields.forEach(field => {
                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    div.innerHTML = '<label class="form-label">' + field.label + (field.required ? ' <span class="text-danger">*</span>' : '') + '</label>' +
                        '<input type="' + (field.type || 'text') + '" name="credentials[' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + '>';
                    credsFields.appendChild(div);
                });
            } else {
                credsCard.style.display = 'none';
            }
        });
});
</script>
@endsection
