@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-carrier-form-page', 'pageClass' => 'admin-shipping-carrier-form-page'])
@section('page-title', $isEditing ? 'Edit Carrier' : 'Add Carrier')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => $isEditing ? 'Edit Carrier' : 'Add Carrier'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3"><div class="ap-page-header-row"><h1 class="ap-page-title">{{ $isEditing ? 'Edit Carrier' : 'Add Carrier' }}</h1></div></div>
    <div class="col-lg-7 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.shipping-carriers.update', $carrier) : route('admin.settings.shipping-carriers.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="carrier-name">Carrier name</label>
                        <input id="carrier-name" name="name" class="form-control" value="{{ old('name', $carrier->name) }}" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label for="delivery-estimate">Delivery estimate</label>
                        <input id="delivery-estimate" name="delivery_estimate" class="form-control" value="{{ old('delivery_estimate', $carrier->delivery_estimate) }}" maxlength="100" placeholder="e.g. 3-5 business days">
                    </div>
                    <div class="form-group">
                        <label for="sort-order">Display order</label>
                        <input id="sort-order" type="number" min="0" max="65535" name="sort_order" class="form-control" value="{{ old('sort_order', $carrier->sort_order ?? 0) }}">
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="carrier-active" type="checkbox" name="is_active" value="1" class="custom-control-input" @checked(old('is_active', $isEditing ? $carrier->is_active : true))>
                        <label class="custom-control-label" for="carrier-active">Active and available at checkout</label>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Carrier' : 'Save Carrier' }}</button>
                    <a href="{{ route('admin.settings.shipping-carriers.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
