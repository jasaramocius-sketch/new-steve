@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-google-firebase-page', 'pageClass' => 'admin-google-firebase-page'])
@section('page-title', 'Google Firebase')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Google Firebase - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header"><h2 class="h6 mb-0">Google Firebase (Push Notifications)</h2></div>
        <div class="card-body">
            <form action="{{ route('admin.settings.google-firebase.update') }}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group row align-items-center">
                    <label class="col-md-3 col-form-label" for="firebase-enabled">Enable Firebase</label>
                    <div class="col-md-9">
                        <input type="hidden" name="firebase_enabled" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" id="firebase-enabled" type="checkbox" name="firebase_enabled" value="1" @checked(filter_var($settings['firebase_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-api-key">API Key</label>
                    <div class="col-md-9"><input id="firebase-api-key" type="text" name="firebase_api_key" class="form-control" value="{{ old('firebase_api_key', $settings['firebase_api_key'] ?? '') }}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-auth-domain">Auth Domain</label>
                    <div class="col-md-9"><input id="firebase-auth-domain" type="text" name="firebase_auth_domain" class="form-control" value="{{ old('firebase_auth_domain', $settings['firebase_auth_domain'] ?? '') }}" placeholder="project-id.firebaseapp.com"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-project-id">Project ID</label>
                    <div class="col-md-9"><input id="firebase-project-id" type="text" name="firebase_project_id" class="form-control" value="{{ old('firebase_project_id', $settings['firebase_project_id'] ?? '') }}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-sender-id">Messaging Sender ID</label>
                    <div class="col-md-9"><input id="firebase-sender-id" type="text" name="firebase_sender_id" class="form-control" value="{{ old('firebase_sender_id', $settings['firebase_sender_id'] ?? '') }}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-app-id">App ID</label>
                    <div class="col-md-9"><input id="firebase-app-id" type="text" name="firebase_app_id" class="form-control" value="{{ old('firebase_app_id', $settings['firebase_app_id'] ?? '') }}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="firebase-server-key">Server Key / FCM Key</label>
                    <div class="col-md-9">
                        <input id="firebase-server-key" type="password" name="firebase_server_key" class="form-control" value="" autocomplete="new-password" placeholder="{{ $serverKeyConfigured ? 'Saved securely; leave blank to keep current' : 'Enter server key' }}">
                        @if ($serverKeyConfigured) <small class="text-muted">Leave blank to keep the current key.</small> @endif
                    </div>
                </div>
                <div class="text-right"><button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
