@extends('backend.layouts.app')
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

    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->
    <div class="alert alert-info">
        Credentials are encrypted when saved. A gateway appears at checkout only when its credentials are complete here and its matching toggle is ON under Settings &rsaquo; Features. Orders stay unpaid until the provider confirms the payment.
    </div>

    <div class="payment-methods-fields">
        @foreach ($settings as $method)
            @php
                $configuredSecrets = collect($method['fields'])
                    ->filter(fn ($f) => ! empty($f['secret']) && ! empty($f['configured']))
                    ->values();
            @endphp
            <div class="payment-methods-fields-col">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2 class="h6 mb-0">{{ $method['title'] }}</h2>
                        @foreach($configuredSecrets as $secretField)
                            @php $secretInputId = $method['provider'].'-'.$secretField['name']; @endphp
                            <div class="action-buttons">
                                <button class="action-btn btn-view toggle-secret-visibility" type="button" data-target="{{ $secretInputId }}" aria-label="Show {{ $secretField['label'] }}" title="Show {{ $secretField['label'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="action-btn btn-edit toggle-secret-edit" type="button" data-target="{{ $secretInputId }}" aria-label="Change {{ $secretField['label'] }}" title="Change {{ $secretField['label'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.payment-methods.update', $method['provider']) }}" method="POST" autocomplete="off" class="payment-method-form">
                            @csrf
                            @foreach ($method['fields'] as $field)
                                @php
                                    $isSecret = (bool) ($field['secret'] ?? false);
                                    $hasSavedValue = $isSecret && ($field['configured'] ?? false);
                                    $displayValue = old($field['name'], $field['value'] ?? '');
                                    $placeholder = $isSecret ? 'Enter '.$field['label'] : 'Enter '.$field['label'];
                                @endphp
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 col-form-label" for="{{ $method['provider'] }}-{{ $field['name'] }}">{{ $field['label'] }}</label>
                                    <div class="col-md-8">
                                        @if ($isSecret)
                                            <input
                                                id="{{ $method['provider'] }}-{{ $field['name'] }}"
                                                type="password"
                                                class="form-control @error($field['name']) is-invalid @enderror"
                                                name="{{ $field['name'] }}"
                                                value="{{ $displayValue }}"
                                                placeholder="{{ $placeholder }}"
                                                autocomplete="new-password"
                                                data-lpignore="true" data-1p-ignore
                                                @if ($hasSavedValue) readonly @endif
                                                @required(($field['required'] ?? false) && ! $hasSavedValue)
                                            >
                                        @else
                                            <input
                                                id="{{ $method['provider'] }}-{{ $field['name'] }}"
                                                type="{{ $field['type'] ?? 'text' }}"
                                                class="form-control @error($field['name']) is-invalid @enderror"
                                                name="{{ $field['name'] }}"
                                                value="{{ $displayValue }}"
                                                placeholder="{{ $placeholder }}"
                                                autocomplete="off"
                                                @required($field['required'] ?? false)
                                            >
                                        @endif
                                    </div>
                                    <div class="col">

                                        @error($field['name']) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @if ($hasSavedValue) <small class="text-muted d-block mt-1">Saved securely. Field is read-only; click the edit icon to update.</small> @endif
                                        @if (!empty($field['help'])) <div class="alert alert-primary mt-2 mb-0" role="note">{{ $field['help'] }}</div> @endif
                                    </div>
                                </div>
                            @endforeach

                            @if ($method['sandbox'])
                                <div class="form-group row">
                                    <div class="col-md-4"><label class="col-form-label" for="{{ $method['provider'] }}-sandbox">{{ $method['title'] }} Sandbox Mode</label></div>
                                    <div class="col-md-8 d-flex align-items-center">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" id="{{ $method['provider'] }}-sandbox" type="checkbox" role="switch" name="{{ $method['sandbox'] }}" value="1" @checked($method['sandboxEnabled'])>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group mb-0 text-right">
                                <button type="submit" class="btn btn-primary  d-flex align-items-center gap-1"> <i class="fas fa-save"></i>Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var eyeSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeSlashSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    var penSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    var timesSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

    document.querySelectorAll('.toggle-secret-visibility').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.innerHTML = isPassword ? eyeSlashSvg : eyeSvg;
            var label = isPassword ? 'Hide secret' : 'Show secret';
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
        });
    });

    document.querySelectorAll('.toggle-secret-edit').forEach(function (btn) {
        var input = document.getElementById(btn.dataset.target);
        if (!input) return;
        input.dataset.originalValue = input.value;
        btn.addEventListener('click', function () {
            var isReadonly = input.hasAttribute('readonly');
            if (isReadonly) {
                input.removeAttribute('readonly');
                input.value = '';
                input.placeholder = 'Enter new secret key';
                input.focus();
                btn.innerHTML = timesSvg;
                btn.setAttribute('aria-label', 'Cancel change');
                btn.setAttribute('title', 'Cancel change');
            } else {
                input.setAttribute('readonly', 'readonly');
                input.value = input.dataset.originalValue || '';
                input.type = 'password';
                btn.innerHTML = penSvg;
                btn.setAttribute('aria-label', 'Change secret');
                btn.setAttribute('title', 'Change secret');
                var visButton = btn.parentElement.querySelector('.toggle-secret-visibility');
                if (visButton) {
                    visButton.innerHTML = eyeSvg;
                    visButton.setAttribute('aria-label', 'Show secret');
                    visButton.setAttribute('title', 'Show secret');
                }
            }
        });
    });
});
</script>
@endsection
