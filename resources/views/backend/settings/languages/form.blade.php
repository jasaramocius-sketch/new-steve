@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-languages-form-page', 'pageClass' => 'admin-languages-form-page'])
@section('page-title', 'Add New Language')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add New Language | ' . config('app.name', 'Steve Autoparts'),
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="col-lg-6 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.languages.update', $language) : route('admin.settings.languages.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="language-name" class="form-label mt-0">Name</label>
                        <input id="language-name" class="form-control" name="name" value="{{ old('name', $language->name) }}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="language-code" class="form-label">Code</label>
                        <input id="language-code" class="form-control" name="code" value="{{ old('code', $language->code) }}" required maxlength="12" pattern="[a-z]{2,3}(_[A-Z]{2})?" @readonly($isEditing && $language->code === 'en') placeholder="en or pt_BR">
                    </div>
                    <div class="form-group">
                        <label for="flutter-code" class="form-label">Flutter App Lang Code</label>
                        <input id="flutter-code" class="form-control" name="flutter_code" value="{{ old('flutter_code', $language->flutter_code) }}" maxlength="12">
                    </div>
                    <div class="custom-control custom-checkbox mb-2 mt-2">
                        <input id="language-rtl" class="custom-control-input" type="checkbox" name="rtl" value="1" @checked(old('rtl', $language->rtl))>
                        <label class="custom-control-label" for="language-rtl">Right-to-left language</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="language-active" class="custom-control-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEditing ? $language->is_active : true))>
                        <label class="custom-control-label" for="language-active">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $isEditing ? 'Update' : 'Save' }}</button>
                    <a href="{{ route('admin.settings.languages.index') }}" class="btn btn-primary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
