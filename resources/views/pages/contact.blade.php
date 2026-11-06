@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-contact-page', 'pageClass' => 'user-contact-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? (($page->title ?? 'Contact') . ' - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? null,
        'metaDescription' => $page->meta_description ?? null,
        'robots' => 'index, follow',
    ])
@endsection

@section('content')

@php
    $contactAddress = \App\Models\Setting::get('header_address', config('contact.address'));
    $contactPhone = \App\Models\Setting::get('header_phone', config('contact.phone'));
    $contactEmail = \App\Models\Setting::get('header_email', config('contact.email'));
    $contactWorkingHours = \App\Models\Setting::get('contact_working_hours', config('contact.working_hours'));
    $contactMapEmbed = \App\Models\Setting::get('contact_map_embed', config('contact.map_embed'));
    $contactPhoneHref = preg_replace('/[^0-9+]/', '', (string) $contactPhone);
@endphp

<section class="contect-page-main-section">
    <div class="container">
        @if (!empty($page->content))
            <div class="page-content cms-contact-intro">
                {!! $page->content !!}
            </div>
        @else
            <div class="text-center mb-5">
                <h1>Contact Us</h1>
                <p class="text-muted">
                    Have questions? We'd love to hear from you.
                </p>
            </div>
        @endif
        <div class="row g-4 contact-form-row">
            <!-- Contact Form -->
            <div class="col-lg-6 contact-form-col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <input type="text" name="name" class="form-control mb-3" placeholder="Name" required>
                            <input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>
                            <input type="tel" name="phone" class="form-control mb-3" inputmode="numeric" placeholder="Phone" required>
                            <input type="text" name="subject" class="form-control mb-3" placeholder="Subject" required>
                            <textarea name="message" class="form-control textarea-control texteditor mb-3" rows="5" placeholder="Message" required></textarea>
                            <button type="submit" class="btn btn-primary steve-btn">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Contact Info -->
            <div class="col-lg-6 contact-info-col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Contact Information</h4>
                        @if (filled($contactAddress))
                            <div class="mb-4 d-flex gap-2 ">
                                <div class="">
                                <i class="fas fa-map-marker-alt me-2 w-fit"></i>
                                </div>
                                <div class="">
                                <strong>Address</strong>
                                <p class="text-muted mb-0">
                                    {!! nl2br(e($contactAddress)) !!}
                                </p></div>
                            </div>
                        @endif
                        @if (filled($contactPhone))
                            <div class="mb-4 d-flex gap-2 ">
                                <div class="">
                                <i class="fas fa-phone me-2 w-fit"></i>
                                </div>
                                <div class="">
                                <strong>Phone</strong></br>
                                <a href="tel:{{ $contactPhoneHref }}" class="text-muted mb-0 a-tag-hover-color-red">
                                    {{ $contactPhone }}
                                </a>
                            </div>
                            </div>
                        @endif
                        @if (filled($contactEmail))
                            <div class="mb-4 d-flex gap-2 ">
                                <div class="">
                                <i class="fas fa-envelope me-2 w-fit"></i>
                                </div>
                                <div class="">
                                <strong>Email Address</strong></br>
                                <a href="mailto:{{ $contactEmail }}" class="text-muted mb-0 a-tag-hover-color-red">
                                    {{ $contactEmail }}
                                </a>
                            </div>
                            </div>
                        @endif
                        @if (filled($contactWorkingHours))
                            <div class="mb-4 d-flex gap-2 ">
                                <div class="">
                                <i class="fas fa-clock me-2 w-fit"></i>
                                </div>
                                <div class="">
                                <strong>Working Hours</strong>
                                <p class="text-muted mb-0">
                                    {!! nl2br(e($contactWorkingHours)) !!}
                                </p>
                            </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Google Map -->
        @if (filled($contactMapEmbed))
            <div class="mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <iframe src="{{ $contactMapEmbed }}" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@endsection