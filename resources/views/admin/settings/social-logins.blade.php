@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-social-logins-page', 'pageClass' => 'admin-social-logins-page'])
@section('page-title', 'Social media Logins')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Social media Logins - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="alert alert-info mb-3 mb-0">Credentials are stored for social login integration. Ensure corresponding OAuth routes/controllers are configured if you enable these providers.</div>

    <form action="{{ route('admin.settings.social-logins.update') }}" method="POST" autocomplete="off">
        @csrf
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h2 class="h6 mb-0">Google Login Credential</h2></div>
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label m-0" for="google-login-enabled">Enable</label>
                            <div class="col-md-8">
                                <input type="hidden" name="google_login_enabled" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" id="google-login-enabled" type="checkbox" name="google_login_enabled" value="1" @checked(filter_var($settings['google_login'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="google-client-id">Client ID</label>
                            <div class="col-md-8"><input id="google-client-id" type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $settings['google_client_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="google-client-secret">Client Secret</label>
                            <div class="col-md-8">
                                <input id="google-client-secret" type="password" name="google_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ $googleClientSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Enter client secret' }}">
                                @if ($googleClientSecretConfigured) <small class="text-muted">Leave blank to keep the current secret.</small> @endif
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-md-4 col-form-label m-0" for="google-redirect">Redirect URL</label>
                            <div class="col-md-8"><input id="google-redirect" type="text" name="google_redirect" class="form-control" value="{{ old('google_redirect', $settings['google_redirect'] ?? '') }}" placeholder="{{ url('/auth/google/callback') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h2 class="h6 mb-0">Facebook Login Credential</h2></div>
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label m-0" for="facebook-login-enabled">Enable</label>
                            <div class="col-md-8">
                                <input type="hidden" name="facebook_login_enabled" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" id="facebook-login-enabled" type="checkbox" name="facebook_login_enabled" value="1" @checked(filter_var($settings['facebook_login'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="facebook-client-id">App ID</label>
                            <div class="col-md-8"><input id="facebook-client-id" type="text" name="facebook_client_id" class="form-control" value="{{ old('facebook_client_id', $settings['facebook_client_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="facebook-client-secret">App Secret</label>
                            <div class="col-md-8">
                                <input id="facebook-client-secret" type="password" name="facebook_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ $facebookClientSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Enter app secret' }}">
                                @if ($facebookClientSecretConfigured) <small class="text-muted">Leave blank to keep the current secret.</small> @endif
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-md-4 col-form-label m-0" for="facebook-redirect">Redirect URL</label>
                            <div class="col-md-8"><input id="facebook-redirect" type="text" name="facebook_redirect" class="form-control" value="{{ old('facebook_redirect', $settings['facebook_redirect'] ?? '') }}" placeholder="{{ url('/auth/facebook/callback') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h2 class="h6 mb-0">Twitter Login Credential</h2></div>
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label m-0" for="twitter-login-enabled">Enable</label>
                            <div class="col-md-8">
                                <input type="hidden" name="twitter_login_enabled" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" id="twitter-login-enabled" type="checkbox" name="twitter_login_enabled" value="1" @checked(filter_var($settings['twitter_login'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="twitter-client-id">Client ID</label>
                            <div class="col-md-8"><input id="twitter-client-id" type="text" name="twitter_client_id" class="form-control" value="{{ old('twitter_client_id', $settings['twitter_client_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="twitter-client-secret">Client Secret</label>
                            <div class="col-md-8">
                                <input id="twitter-client-secret" type="password" name="twitter_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ $twitterClientSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Enter client secret' }}">
                                @if ($twitterClientSecretConfigured) <small class="text-muted">Leave blank to keep the current secret.</small> @endif
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-md-4 col-form-label m-0" for="twitter-redirect">Redirect URL</label>
                            <div class="col-md-8"><input id="twitter-redirect" type="text" name="twitter_redirect" class="form-control" value="{{ old('twitter_redirect', $settings['twitter_redirect'] ?? '') }}" placeholder="{{ url('/auth/twitter/callback') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h2 class="h6 mb-0">Apple Login Credential</h2></div>
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label m-0" for="apple-login-enabled">Enable</label>
                            <div class="col-md-8">
                                <input type="hidden" name="apple_login_enabled" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" id="apple-login-enabled" type="checkbox" name="apple_login_enabled" value="1" @checked(filter_var($settings['apple_login'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="apple-client-id">Service ID (Client ID)</label>
                            <div class="col-md-8"><input id="apple-client-id" type="text" name="apple_client_id" class="form-control" value="{{ old('apple_client_id', $settings['apple_client_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="apple-team-id">Team ID</label>
                            <div class="col-md-8"><input id="apple-team-id" type="text" name="apple_team_id" class="form-control" value="{{ old('apple_team_id', $settings['apple_team_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="apple-key-id">Key ID</label>
                            <div class="col-md-8"><input id="apple-key-id" type="text" name="apple_key_id" class="form-control" value="{{ old('apple_key_id', $settings['apple_key_id'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label m-0" for="apple-client-secret">Client Secret / Private Key</label>
                            <div class="col-md-8">
                                <textarea id="apple-client-secret" name="apple_client_secret" class="form-control textarea-control" rows="3" autocomplete="new-password" placeholder="{{ $appleClientSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Paste generated client secret/JWT or private key material as required' }}"></textarea>
                                @if ($appleClientSecretConfigured) <small class="text-muted">Leave blank to keep the current value.</small> @endif
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-md-4 col-form-label m-0" for="apple-redirect">Redirect URL</label>
                            <div class="col-md-8"><input id="apple-redirect" type="text" name="apple_redirect" class="form-control" value="{{ old('apple_redirect', $settings['apple_redirect'] ?? '') }}" placeholder="{{ url('/auth/apple/callback') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right"><button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div>
@endsection
