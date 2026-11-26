@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-sellers-edit-page', 'pageClass' => 'admin-sellers-edit-page'])
@section('page-title', 'Edit Seller')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Seller - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@include('backend.sellers._form', [
    'action' => route('admin.sellers.update', $seller->id),
    'method' => 'PUT',
    'submitLabel' => 'Update',
    'seller' => $seller,
])

@endsection
