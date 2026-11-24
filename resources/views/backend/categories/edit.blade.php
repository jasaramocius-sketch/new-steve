@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-edit-category-page', 'pageClass' => 'admin-edit-category-page'])
@section('page-title', 'Edit Category')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Category - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('backend.categories._form', [
    'action' => route('admin.categories.update', $category->id),
    'method' => 'PUT',
    'submitLabel' => 'Update',
    'category' => $category,
    'parents' => $parents,
    'excluded' => $excluded,
])

@endsection
