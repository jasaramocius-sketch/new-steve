@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-tax-type-form-page', 'pageClass' => 'admin-tax-type-form-page'])
@section('page-title', $isEditing ? 'Edit Tax Type' : 'Add Tax Type')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => $isEditing ? 'Edit Tax Type' : 'Add Tax Type'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <div class="ap-page-header-row">
            <h1 class="ap-page-title">{{ $isEditing ? 'Edit Tax Type' : 'Add Tax Type' }}</h1>
        </div>
    </div>
    <div class="col-lg-6 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.tax-types.update', $taxType) : route('admin.settings.tax-types.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="tax-type-name">Tax Type</label>
                        <input id="tax-type-name" name="name" class="form-control" value="{{ old('name', $taxType->name) }}" maxlength="150" required autofocus>
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="tax-type-active" type="checkbox" name="is_active" value="1" class="custom-control-input" @checked(old('is_active', $isEditing ? $taxType->is_active : true))>
                        <label class="custom-control-label" for="tax-type-active">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Tax Type' : 'Save Tax Type' }}</button>
                    <a href="{{ route('admin.settings.custom') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
