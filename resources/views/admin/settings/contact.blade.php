@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-contact-settings-page', 'pageClass' => 'admin-contact-settings-page'])
@section('page-title', 'Contact Information')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Contact Information - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="mb-1">Contact Information</h4>
            <p class="text-muted mb-0">Manage the details shown on the Contact Us page. Phone, email and address are also used in the site header and footer.</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> All Settings
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('admin.settings.contact.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label" for="header_phone"><strong>Phone Number</strong></label>
                                    <input id="header_phone" type="text" name="header_phone" class="form-control @error('header_phone') is-invalid @enderror"
                                        maxlength="50"
                                        value="{{ old('header_phone', $settings['header_phone'] ?? config('contact.phone')) }}">
                                    @error('header_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label" for="header_email"><strong>Email Address</strong></label>
                                    <input id="header_email" type="email" name="header_email" class="form-control @error('header_email') is-invalid @enderror"
                                        maxlength="255"
                                        value="{{ old('header_email', $settings['header_email'] ?? config('contact.email')) }}">
                                    @error('header_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="header_address"><strong>Address</strong></label>
                            <textarea id="header_address" name="header_address" rows="3" maxlength="1000"
                                class="form-control textarea-control @error('header_address') is-invalid @enderror">{{ old('header_address', $settings['header_address'] ?? config('contact.address')) }}</textarea>
                            <small class="text-muted">Line breaks will be preserved on the Contact Us page.</small>
                            @error('header_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="contact_working_hours"><strong>Working Hours</strong></label>
                            <textarea id="contact_working_hours" name="contact_working_hours" rows="3" maxlength="1000"
                                class="form-control textarea-control @error('contact_working_hours') is-invalid @enderror">{{ old('contact_working_hours', $settings['contact_working_hours'] ?? config('contact.working_hours')) }}</textarea>
                            <small class="text-muted">Line breaks will be preserved on the Contact Us page.</small>
                            @error('contact_working_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="contact_map_embed"><strong>Google Maps Embed URL</strong></label>
                            <textarea id="contact_map_embed" name="contact_map_embed" rows="4" maxlength="2000"
                                class="form-control textarea-control @error('contact_map_embed') is-invalid @enderror">{{ old('contact_map_embed', $settings['contact_map_embed'] ?? config('contact.map_embed')) }}</textarea>
                            <small class="text-muted">
                                Paste the Google Maps embed URL (the <code>src</code> value from the iframe), or the complete iframe code. Leave blank to hide the map.
                            </small>
                            @error('contact_map_embed') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary steve-btn">
                                <i class="fas fa-save me-1"></i> Save Contact Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-circle-info me-1"></i> Where this appears</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Contact Us page &mdash; Contact Information block and map</li>
                        <li class="mb-2">Site header &mdash; phone and email</li>
                        <li class="mb-0">Site footer &mdash; phone, email and address</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
