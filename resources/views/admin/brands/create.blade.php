@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-brands-create-page', 'pageClass' => 'admin-brands-create-page'])
@section('page-title', 'Add Brand')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add Brand - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('admin.brands._form', [
    'action' => route('admin.brands.store'),
    'method' => 'POST',
    'submitLabel' => 'Save',
    'brand' => null,
])

@endsection
