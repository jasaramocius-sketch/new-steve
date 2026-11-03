@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <a href="{{ route('admin.settings.pickup-points.index') }}">Pickup Points</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">{{ $isEditing ? 'Edit Pickup Point' : 'Add New Pick-up Point' }}</span>
        </nav>
        <div class="ap-page-header-row"><h1 class="ap-page-title">{{ $isEditing ? 'Edit Pickup Point' : 'Add New Pick-up Point' }}</h1></div>
    </div>

    <div class="col-lg-6 px-0">
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
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update' : 'Save' }}</button>
                    <a href="{{ route('admin.settings.pickup-points.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
