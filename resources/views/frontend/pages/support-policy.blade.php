@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-support-policy-page', 'pageClass' => 'user-support-policy-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? ('Support Policy - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? ('Support Policy | ' . config('app.name', 'StAutoparts')),
        'metaDescription' => $page->meta_description ?? null,
        'robots' => 'index, follow',
    ])
@endsection
@section('content')

<div class="container py-5">
    <h1 class="fw-bold mb-4">Support Policy</h1>
    <p class="text-muted">Last updated: July 2026</p>

    <h5 class="mt-4">1. Support Hours</h5>
    <p>Our support team is available Monday through Friday, 9:00 AM to 6:00 PM (EST). We strive to respond to all inquiries within 24 business hours.</p>

    <h5 class="mt-4">2. How to Get Support</h5>
    <p>You can reach us via our <a href="{{ route('contact') }}">contact form</a> or by email. For order-related inquiries, please include your order number.</p>

    <h5 class="mt-4">3. Scope of Support</h5>
    <p>We provide support for order inquiries, product questions, and technical issues related to our website. Support is available in English.</p>

    <h5 class="mt-4">4. Escalation</h5>
    <p>If your issue is not resolved to your satisfaction, you may request escalation to a senior support representative.</p>
</div>

@endsection
