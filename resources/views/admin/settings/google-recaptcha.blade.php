@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-google-recaptcha-page', 'pageClass' => 'admin-google-recaptcha-page'])
@section('page-title', 'Google reCAPTCHA')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Google reCAPTCHA - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    @if (($settings['recaptcha_version'] ?? 'v2') === 'v3' && (empty($settings['recaptcha_v3_site_key']) || empty($v3SecretConfigured)))
        <div class="alert alert-warning">reCAPTCHA v3 is selected, but v3 site/secret keys are missing. reCAPTCHA stays disabled on the storefront until both are saved.</div>
    @elseif (($settings['recaptcha_version'] ?? 'v2') === 'v2' && (empty($settings['recaptcha_site_key']) || empty($secretConfigured)))
        <div class="alert alert-warning">reCAPTCHA v2 is selected, but v2 site/secret keys are missing. reCAPTCHA stays disabled on the storefront until both are saved.</div>
    @endif -->

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h6 mb-0">Google reCAPTCHA Settings</h2>
            <form action="{{ route('admin.settings.google-recaptcha.reset') }}" method="POST"
                  onsubmit="return confirm('Reset reCAPTCHA settings? This will remove all saved keys and disable reCAPTCHA on Login, Register, and Contact forms.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-rotate-left me-1"></i>Reset
                </button>
            </form>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.google-recaptcha.update') }}" method="POST">
                @csrf

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="recaptcha-version">reCAPTCHA Version</label>
                    <div class="col-md-9">
                        <select id="recaptcha-version" name="recaptcha_version" class="form-control form-select" required>
                            <option value="v2" @selected(old('recaptcha_version', $settings['recaptcha_version'] ?? 'v2') === 'v2')>v2 - Checkbox / challenge</option>
                            <option value="v3" @selected(old('recaptcha_version', $settings['recaptcha_version'] ?? 'v2') === 'v3')>v3 - Invisible score-based</option>
                        </select>
                        <small class="text-muted">v2 and v3 use different keys. Select which version is active on the storefront.</small>
                    </div>
                </div>

                <div id="recaptcha-v2-fields" class="{{ old('recaptcha_version', $settings['recaptcha_version'] ?? 'v2') === 'v3' ? 'd-none' : '' }}">
                    <hr>

                    <h3 class="h6 mb-3">reCAPTCHA v2 Keys</h3>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="recaptcha-site-key">Site Key</label>
                        <div class="col-md-9">
                            <input id="recaptcha-site-key" type="text" name="recaptcha_site_key" class="form-control" value="{{ old('recaptcha_site_key', $settings['recaptcha_site_key'] ?? '') }}" placeholder="Enter Google reCAPTCHA v2 site key">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="recaptcha-secret-key">Secret Key</label>
                        <div class="col-md-9">
                            <input id="recaptcha-secret-key" type="password" name="recaptcha_secret_key" class="form-control" value="" autocomplete="new-password" placeholder="{{ ($secretConfigured ?? false) ? 'Saved securely; leave blank to keep current' : 'Enter Google reCAPTCHA v2 secret key' }}">
                            @if($secretConfigured ?? false)
                                <small class="text-muted">A v2 secret key is saved. Leave blank to keep it.</small>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="recaptcha-v3-fields" class="{{ old('recaptcha_version', $settings['recaptcha_version'] ?? 'v2') === 'v3' ? '' : 'd-none' }}">
                    <hr>

                    <h3 class="h6 mb-3">reCAPTCHA v3 Keys</h3>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="recaptcha-v3-site-key">Site Key</label>
                        <div class="col-md-9">
                            <input id="recaptcha-v3-site-key" type="text" name="recaptcha_v3_site_key" class="form-control" value="{{ old('recaptcha_v3_site_key', $settings['recaptcha_v3_site_key'] ?? '') }}" placeholder="Enter Google reCAPTCHA v3 site key">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="recaptcha-v3-secret-key">Secret Key</label>
                        <div class="col-md-9">
                            <input id="recaptcha-v3-secret-key" type="password" name="recaptcha_v3_secret_key" class="form-control" value="" autocomplete="new-password" placeholder="{{ ($v3SecretConfigured ?? false) ? 'Saved securely; leave blank to keep current' : 'Enter Google reCAPTCHA v3 secret key' }}">
                            @if($v3SecretConfigured ?? false)
                                <small class="text-muted">A v3 secret key is saved. Leave blank to keep it.</small>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="recaptcha-v3-score">Minimum Score</label>
                        <div class="col-md-9">
                            <input id="recaptcha-v3-score" type="number" name="recaptcha_v3_score" class="form-control" value="{{ old('recaptcha_v3_score', $settings['recaptcha_v3_score'] ?? '0.5') }}" min="0" max="1" step="0.1">
                            <small class="text-muted">0.0 = likely bot, 1.0 = likely human. Recommended: 0.5.</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group row">
                    <div class="col-md-3 col-form-label">Enable on</div>
                    <div class="col-md-9 mt-1">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="recaptcha_login" value="1" id="recaptcha-login" @checked(($settings['recaptcha_login'] ?? '0') == '1')>
                            <label class="form-check-label m-0" for="recaptcha-login">Login</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="recaptcha_register" value="1" id="recaptcha-register" @checked(($settings['recaptcha_register'] ?? '0') == '1')>
                            <label class="form-check-label m-0" for="recaptcha-register">Register</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recaptcha_contact" value="1" id="recaptcha-contact" @checked(($settings['recaptcha_contact'] ?? '0') == '1')>
                            <label class="form-check-label m-0" for="recaptcha-contact">Contact Form</label>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-save"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const versionSelect = document.getElementById('recaptcha-version');
    const v2Fields = document.getElementById('recaptcha-v2-fields');
    const v3Fields = document.getElementById('recaptcha-v3-fields');

    if (!versionSelect || !v2Fields || !v3Fields) return;

    const syncVersionFields = function () {
        const isV3 = versionSelect.value === 'v3';
        v2Fields.classList.toggle('d-none', isV3);
        v3Fields.classList.toggle('d-none', !isV3);
    };

    versionSelect.addEventListener('change', syncVersionFields);
    syncVersionFields();
});
</script>
@endpush
