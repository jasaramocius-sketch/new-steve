@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-api-integrations-edit-page', 'pageClass' => 'admin-api-integrations-edit-page'])
@section('page-title', 'Edit API Integration')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit API Integration - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Edit API Integration</h4>
        <p class="text-muted mb-0 small">{{ $apiIntegration->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.api-integrations.test', $apiIntegration->id) }}" method="POST">
            @csrf
            <button class="btn btn-outline-info"><i class="fas fa-plug me-1"></i> Test Connection</button>
        </form>
        <a href="{{ route('admin.api-integrations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<form action="{{ route('admin.api-integrations.update', $apiIntegration->id) }}" method="POST">
    @csrf
    @method('PUT')
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
                            <select name="type" class="form-select" required>
                                <option value="courier" {{ $apiIntegration->type == 'courier' ? 'selected' : '' }}>Courier / Shipping</option>
                                <option value="payment" {{ $apiIntegration->type == 'payment' ? 'selected' : '' }}>Payment Gateway</option>
                                <option value="sms" {{ $apiIntegration->type == 'sms' ? 'selected' : '' }}>SMS</option>
                                <option value="whatsapp" {{ $apiIntegration->type == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="email" {{ $apiIntegration->type == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="other" {{ $apiIntegration->type == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Provider <span class="text-danger">*</span></label>
                            <input type="text" name="provider" class="form-control" value="{{ $apiIntegration->provider }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $apiIntegration->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Auth Type <span class="text-danger">*</span></label>
                            <select name="auth_type" class="form-select" required>
                                <option value="bearer" {{ $apiIntegration->auth_type == 'bearer' ? 'selected' : '' }}>Bearer Token</option>
                                <option value="api_key" {{ $apiIntegration->auth_type == 'api_key' ? 'selected' : '' }}>API Key</option>
                                <option value="basic" {{ $apiIntegration->auth_type == 'basic' ? 'selected' : '' }}>Basic Auth</option>
                                <option value="oauth" {{ $apiIntegration->auth_type == 'oauth' ? 'selected' : '' }}>OAuth</option>
                                <option value="none" {{ $apiIntegration->auth_type == 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Base URL</label>
                            <input type="url" name="base_url" class="form-control" value="{{ $apiIntegration->base_url }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control textarea-control" rows="2">{{ $apiIntegration->description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">API Credentials</h5>
                    <small class="text-muted">Leave blank to keep existing values</small>
                </div>
                <div class="card-body">
                    @php $creds = $apiIntegration->credentials; @endphp
                    @if($apiIntegration->provider == 'shiprocket')
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="credentials[email]" class="form-control" value="{{ $creds['email'] ?? '' }}"></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="credentials[password]" class="form-control" placeholder="Enter new password to update"></div>
                        <div class="mb-3"><label class="form-label">Pickup Location</label><input type="text" name="credentials[pickup_location]" class="form-control" value="{{ $creds['pickup_location'] ?? '' }}"></div>
                    @elseif($apiIntegration->provider == 'delhivery')
                        <div class="mb-3"><label class="form-label">API Key / Token</label><input type="text" name="credentials[api_key]" class="form-control" value="{{ $creds['api_key'] ?? '' }}"></div>
                        <div class="mb-3"><label class="form-label">Client ID</label><input type="text" name="credentials[client_id]" class="form-control" value="{{ $creds['client_id'] ?? '' }}"></div>
                    @elseif($apiIntegration->provider == 'razorpay')
                        <div class="mb-3"><label class="form-label">Key ID</label><input type="text" name="credentials[key_id]" class="form-control" value="{{ $creds['key_id'] ?? '' }}"></div>
                        <div class="mb-3"><label class="form-label">Key Secret</label><input type="password" name="credentials[key_secret]" class="form-control" placeholder="Enter new secret to update"></div>
                    @else
                        @foreach($creds as $k => $v)
                            <div class="mb-3">
                                <label class="form-label">{{ ucwords(str_replace('_', ' ', $k)) }}</label>
                                <input type="{{ in_array($k, ['password','secret_key','api_secret','access_token']) ? 'password' : 'text' }}" name="credentials[{{ $k }}]" class="form-control" value="{{ $v }}" placeholder="Enter new value to update">
                            </div>
                        @endforeach
                        <div class="mb-3"><label class="form-label">API Key / Token</label><input type="text" name="credentials[api_key]" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Secret Key</label><input type="password" name="credentials[secret_key]" class="form-control"></div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Endpoints (Optional)</h5>
                </div>
                <div class="card-body">
                    @php $endpoints = $apiIntegration->endpoints; @endphp
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Create Shipment</label><input type="text" name="endpoints[create_shipment]" class="form-control" value="{{ $endpoints['create_shipment'] ?? '' }}"></div>
                        <div class="col-md-6"><label class="form-label">Track Order</label><input type="text" name="endpoints[track]" class="form-control" value="{{ $endpoints['track'] ?? '' }}"></div>
                        <div class="col-md-6"><label class="form-label">Cancel Shipment</label><input type="text" name="endpoints[cancel]" class="form-control" value="{{ $endpoints['cancel'] ?? '' }}"></div>
                        <div class="col-md-6"><label class="form-label">Get Couriers</label><input type="text" name="endpoints[couriers]" class="form-control" value="{{ $endpoints['couriers'] ?? '' }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $apiIntegration->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Activate Integration</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ $apiIntegration->is_default ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Set as Default</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ $apiIntegration->priority }}" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 steve-btn"><i class="fas fa-save me-1"></i> Update Integration</button>
                </div>
            </div>

            @if($apiIntegration->last_tested_at)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Last Test Result</h6>
                </div>
                <div class="card-body">
                    <small class="d-block text-muted mb-2">{{ $apiIntegration->last_tested_at->format('d-m-Y H:i:s') }}</small>
                    <div class="small">{{ $apiIntegration->last_test_result }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>
@endsection
