@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-payment-methods-page', 'pageClass' => 'admin-payment-methods-page'])
@section('page-title', 'Payment Methods')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Payment Methods',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Payment Methods</span>
        </nav>
        <div class="ap-page-header-row"><h1 class="ap-page-title">Payment Methods</h1></div>
    </div> -->

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <div class="alert alert-info">
        Credentials are encrypted when saved. The Stripe keys are used by the hosted Stripe Checkout at checkout (enable Stripe Payment in System Settings). The other gateways are stored for future use and are not yet active at checkout.
    </div>

    <div class="row">
        @foreach ($settings as $method)
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h2 class="h6 mb-0">{{ $method['title'] }}</h2></div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.payment-methods.update', $method['provider']) }}" method="POST" autocomplete="off" class="payment-method-form">
                            @csrf
                            @foreach ($method['fields'] as $field)
                                @php
                                    $isSecret = (bool) ($field['secret'] ?? false);
                                    $hasSavedValue = $isSecret && ($field['configured'] ?? false);
                                    // Never echo a secret back into the DOM. Non-secret fields keep what was just typed.
                                    $displayValue = $isSecret ? '' : old($field['name'], $field['value']);
                                    // Show the masked hint inside the field itself when a secret is already saved.
                                    $placeholder = $isSecret
                                        ? ($hasSavedValue ? $field['masked'] : 'Enter '.$field['label'])
                                        : 'Enter '.$field['label'];
                                @endphp
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 col-form-label" for="{{ $method['provider'] }}-{{ $field['name'] }}">{{ $field['label'] }}</label>
                                    <div class="col-md-8">
                                        <input
                                            id="{{ $method['provider'] }}-{{ $field['name'] }}"
                                            type="{{ $isSecret ? 'password' : ($field['type'] ?? 'text') }}"
                                            class="form-control @error($field['name']) is-invalid @enderror"
                                            name="{{ $field['name'] }}"
                                            value="{{ $displayValue }}"
                                            placeholder="{{ $placeholder }}"
                                            autocomplete="{{ $isSecret ? 'new-password' : 'off' }}"
                                            @if ($isSecret) data-lpignore="true" data-1p-ignore @endif
                                            @required(($field['required'] ?? false) && ! $hasSavedValue)
                                        >
                                    </div>
                                    <div class="col">

                                        @error($field['name']) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @if ($hasSavedValue) <small class="text-muted d-block mt-1">Saved securely. Leave blank to keep the current secret; the full value is never displayed.</small> @endif
                                        @if (!empty($field['help'])) <div class="alert alert-primary mt-2 mb-0" role="note">{{ $field['help'] }}</div> @endif
                                    </div>
                                </div>
                            @endforeach

                            @if ($method['sandbox'])
                                <div class="form-group row">
                                    <div class="col-md-4"><label class="col-form-label" for="{{ $method['provider'] }}-sandbox">{{ $method['title'] }} Sandbox Mode</label></div>
                                    <div class="col-md-8 d-flex align-items-center">
                                        <div class="custom-control custom-switch">
                                            <input id="{{ $method['provider'] }}-sandbox" type="checkbox" class="custom-control-input" name="{{ $method['sandbox'] }}" value="1" @checked($method['sandboxEnabled'])>
                                            <label class="custom-control-label" for="{{ $method['provider'] }}-sandbox">Sandbox</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group mb-0 text-right">
                                <button type="submit" class="btn btn-sm btn-primary  d-flex align-items-center gap-1"> <i class="fas fa-save"></i>Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
