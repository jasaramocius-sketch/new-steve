@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-pickup-points-form-page', 'pageClass' => 'admin-pickup-points-form-page'])
@section('page-title', 'Pickup Points Form (' . ($isEditing ? 'Edit' : 'Add') . ')')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Pickup Points Form',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">

    <div class="col px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.pickup-points.update', $pickupPoint) : route('admin.settings.pickup-points.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="pickup-name">Name</label>
                        <input id="pickup-name" name="name" class="form-control" value="{{ old('name', $pickupPoint->name) }}" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label for="pickup-manager">Manager</label>
                        <input id="pickup-manager" name="manager" class="form-control" value="{{ old('manager', $pickupPoint->manager) }}" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label for="pickup-location">Location</label>
                        <input id="pickup-location" name="location" class="form-control" value="{{ old('location', $pickupPoint->location) }}" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="pickup-contact">Pickup Station Contact</label>
                        <input id="pickup-contact" name="contact" class="form-control" value="{{ old('contact', $pickupPoint->contact) }}" maxlength="50" required>
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="pickup-open" type="checkbox" name="is_open" value="1" class="custom-control-input" @checked(old('is_open', $isEditing ? $pickupPoint->is_open : true))>
                        <label class="custom-control-label" for="pickup-open">Open for pickup</label>
                    </div>
                    <div class="form-group mb-0 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"> <i class="fas fa-save"></i>{{ $isEditing ? 'Update' : 'Save' }}</button>
                        <a href="{{ route('admin.settings.pickup-points.index') }}" class="btn btn-primary d-flex align-items-center gap-1"> <i class="fas fa-times"></i>Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
