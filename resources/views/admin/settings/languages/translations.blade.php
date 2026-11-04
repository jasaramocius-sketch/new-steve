@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-languages-page', 'pageClass' => 'admin-languages-page'])
@section('page-title', 'Languages Translations')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Languages Translations',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <a href="{{ route('admin.settings.languages.index') }}">Language</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">{{ $language->name }} Translations</span>
        </nav>
        <div class="ap-page-header-row">
            <h1 class="ap-page-title">{{ $language->name }} Translations</h1>
            <div class="ap-page-actions"><a href="{{ route('admin.settings.languages.index') }}" class="btn btn-light">Back to Languages</a></div>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header"><h2 class="h6 mb-0">Translation entries</h2></div>
        <div class="card-body">
            <form action="{{ route('admin.settings.languages.translations.update', $language) }}" method="POST">
                @csrf
                @method('PUT')
                @forelse ($translations as $key => $value)
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label" for="translation-{{ $loop->index }}">{{ $key }}</label>
                        <div class="col-lg-9">
                            <textarea id="translation-{{ $loop->index }}" class="form-control textarea-control" name="translations[{{ $key }}]" rows="2">{{ $value }}</textarea>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No translation entries found.</p>
                @endforelse
                @if (count($translations)) <button type="submit" class="btn btn-primary">Save Translations</button> @endif
            </form>
        </div>
    </div>
</div>
@endsection
