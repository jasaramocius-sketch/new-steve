@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-add-category-page', 'pageClass' => 'admin-add-category-page'])
@section('page-title', 'Add Category')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add Category - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('backend.categories._form', [
    'action' => route('admin.categories.store'),
    'method' => 'POST',
    'submitLabel' => 'Save',
    'category' => null,
    'parents' => $parents,
    'excluded' => [],
])

@endsection
