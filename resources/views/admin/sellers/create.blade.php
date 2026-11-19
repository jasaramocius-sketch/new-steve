@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-sellers-create-page', 'pageClass' => 'admin-sellers-create-page'])
@section('page-title', 'Add Seller')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add Seller - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('admin.sellers._form', [
    'action' => route('admin.sellers.store'),
    'method' => 'POST',
    'submitLabel' => 'Save',
    'seller' => null,
])

@endsection
