@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <a href="{{ route('admin.settings.languages.index') }}">Language</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">{{ $isEditing ? 'Edit Language' : 'Add New Language' }}</span>
        </nav>
        <div class="ap-page-header-row"><h1 class="ap-page-title">{{ $isEditing ? 'Edit Language' : 'Add New Language' }}</h1></div>
    </div>

    <div class="col-lg-6 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.languages.update', $language) : route('admin.settings.languages.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="language-name">Name</label>
                        <input id="language-name" class="form-control" name="name" value="{{ old('name', $language->name) }}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="language-code">Code</label>
                        <input id="language-code" class="form-control" name="code" value="{{ old('code', $language->code) }}" required maxlength="12" pattern="[a-z]{2,3}(_[A-Z]{2})?" @readonly($isEditing && $language->code === 'en') placeholder="en or pt_BR">
                    </div>
                    <div class="form-group">
                        <label for="flutter-code">Flutter App Lang Code</label>
                        <input id="flutter-code" class="form-control" name="flutter_code" value="{{ old('flutter_code', $language->flutter_code) }}" maxlength="12">
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input id="language-rtl" class="custom-control-input" type="checkbox" name="rtl" value="1" @checked(old('rtl', $language->rtl))>
                        <label class="custom-control-label" for="language-rtl">Right-to-left language</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="language-active" class="custom-control-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEditing ? $language->is_active : true))>
                        <label class="custom-control-label" for="language-active">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update' : 'Save' }}</button>
                    <a href="{{ route('admin.settings.languages.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
