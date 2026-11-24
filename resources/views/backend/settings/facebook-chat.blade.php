@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-facebook-chat-page', 'pageClass' => 'admin-facebook-chat-page'])
@section('page-title', 'Facebook Chat')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Facebook Chat - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="facebook-chat-page-container d-grid gap-3">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="facebook-chat-page-settings-card card">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Facebook Chat (Messenger) Settings</h2></div>
        <div class="card-body">
            <form action="{{ route('admin.settings.facebook-chat.update') }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                <div class="form-group row align-items-center">
                    <label class="col-md-3 col-form-label m-0" for="facebook-chat-enabled">Enable Facebook Chat</label>
                    <div class="col-md-9">
                        <input type="hidden" name="facebook_chat_enabled" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" id="facebook-chat-enabled" type="checkbox" name="facebook_chat_enabled" value="1" @checked(filter_var($settings['facebook_chat_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-page-id">Facebook Page ID</label>
                    <div class="col-md-9"><input id="facebook-page-id" type="text" name="facebook_page_id" class="form-control" value="{{ old('facebook_page_id', $settings['facebook_page_id'] ?? '') }}" placeholder="Numeric Page ID"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-chat-theme-color">Theme Color</label>
                    <div class="col-md-9"><input id="facebook-chat-theme-color" type="text" name="facebook_chat_theme_color" class="form-control" value="{{ old('facebook_chat_theme_color', $settings['facebook_chat_theme_color'] ?? '') }}" placeholder="#0084FF"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-chat-logged-in-greeting">Logged-in Greeting</label>
                    <div class="col-md-9"><input id="facebook-chat-logged-in-greeting" type="text" name="facebook_chat_logged_in_greeting" class="form-control" value="{{ old('facebook_chat_logged_in_greeting', $settings['facebook_chat_logged_in_greeting'] ?? '') }}" placeholder="Hi! How can we help you?"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-chat-logged-out-greeting">Logged-out Greeting</label>
                    <div class="col-md-9"><input id="facebook-chat-logged-out-greeting" type="text" name="facebook_chat_logged_out_greeting" class="form-control" value="{{ old('facebook_chat_logged_out_greeting', $settings['facebook_chat_logged_out_greeting'] ?? '') }}" placeholder="Hi! Please log in to chat with us."></div>
                </div>
                <div class="text-right"><button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button></div>
            </form>
        </div>
    </div>
    <div class="facebook-chat-page-instructions-card card">
        <div class="card-header bg-white">
            <h6 class="mb-0">Please be carefull when you are configuring Facebook chat. For incorrect configuration you will not get messenger icon on your user-end site.</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0 ps-3">
                <li class="mb-2">1. Login into your facebook page</li>
                <li class="mb-2">2. Find the About option of your facebook page.</li>
                <li class="mb-0">3. At the very bottom, you can find the “Facebook Page ID”.</li>
                <li class="mb-0">4. Go to Settings of your page and find the option of "Advance Messaging".</li>
                <li class="mb-0">5. Scroll down that page and you will get "white listed domain".</li>
                <li class="mb-0">6. Set your website domain name.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
