@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-brands-edit-page', 'pageClass' => 'admin-brands-edit-page'])
@section('page-title', 'Edit Brand')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Brand - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('backend.brands._form', [
    'action' => route('admin.brands.update', $brand->id),
    'method' => 'PUT',
    'submitLabel' => 'Update',
    'brand' => $brand,
])

@endsection
