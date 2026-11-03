@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin.currency-form-page', 'pageClass' => 'admin-currency-form-page'])
@section('page-title', 'Add New Currency')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add New Currency',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <a href="{{ route('admin.settings.currency.index') }}">Currency</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">{{ $isEditing ? 'Edit Currency' : 'Add New Currency' }}</span>
        </nav>
        <div class="ap-page-header-row"><h1 class="ap-page-title">{{ $isEditing ? 'Edit Currency' : 'Add New Currency' }}</h1></div>
    </div> -->

    <div class="col-lg-6 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.currency.update', $currency) : route('admin.settings.currency.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="currency-name">Currency name</label>
                        <input id="currency-name" name="name" class="form-control" value="{{ old('name', $currency->name) }}" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="currency-code">Currency code</label>
                        <input id="currency-code" name="code" class="form-control text-uppercase" value="{{ old('code', $currency->code) }}" minlength="3" maxlength="3" pattern="[A-Za-z]{3}" required>
                    </div>
                    <div class="form-group">
                        <label for="currency-symbol">Currency symbol</label>
                        <input id="currency-symbol" name="symbol" class="form-control" value="{{ old('symbol', $currency->symbol) }}" maxlength="12" required>
                    </div>
                    <div class="form-group">
                        <label for="currency-rate">Exchange Rate (1 USD = ?)</label>
                        <input id="currency-rate" type="number" name="rate" class="form-control" value="{{ old('rate', $currency->rate ?: 1) }}" min="0.00000001" max="100000000" step="any" required>
                    </div>
                    @if (!$isEditing)
                        <div class="custom-control custom-checkbox mb-3">
                            <input id="currency-active" type="checkbox" name="is_active" value="1" class="custom-control-input" checked>
                            <label class="custom-control-label" for="currency-active">Active</label>
                        </div>
                    @endif
                    <div class="form-group mb-0 d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-save"></i>{{ $isEditing ? 'Update' : 'Save' }}</button>
                        <a href="{{ route('admin.settings.currency.index') }}" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
