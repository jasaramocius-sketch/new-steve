@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-google-map-page', 'pageClass' => 'admin-google-map-page'])
@section('page-title', 'Google Map')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Google Map - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header"><h2 class="h6 mb-0">Google Map Settings</h2></div>
        <div class="card-body">
            <form action="{{ route('admin.settings.google-map.update') }}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group row align-items-center">
                    <label class="col-md-3 col-form-label" for="google-map-enabled">Enable Google Map API</label>
                    <div class="col-md-9">
                        <input type="hidden" name="google_map_enabled" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" id="google-map-enabled" type="checkbox" name="google_map_enabled" value="1" @checked(filter_var($settings['google_map_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="google-map-api-key">Google Map API Key</label>
                    <div class="col-md-9">
                        <input id="google-map-api-key" type="password" name="google_map_api_key" class="form-control" value="" autocomplete="new-password" placeholder="{{ $apiKeyConfigured ? 'Saved securely; leave blank to keep current' : 'Enter API key' }}">
                        @if ($apiKeyConfigured) <small class="text-muted">Leave blank to keep the current API key.</small> @endif
                    </div>
                </div>
                <div class="text-right"><button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button></div>
            </form>
        </div>
    </div>
    <div class="alert alert-info mt-3 mb-0">Contact page map embed can also be configured in <a href="{{ route('admin.settings.contact') }}">Contact Information</a> (Google Maps Embed URL).</div>
</div>
@endsection
