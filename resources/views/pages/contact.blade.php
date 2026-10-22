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
                            <textarea name="message" class="form-control texteditor mb-3" rows="5" placeholder="Message" required></textarea>
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
                        <div class="mb-4 d-flex gap-2 ">
                            <div class="">
                            <i class="fas fa-map-marker-alt me-2 w-fit"></i>
                            </div>
                            <div class="">
                            <strong>Address</strong>
                            <p class="text-muted mb-0">
                                123 Business Street,<br>
                                Ahmedabad, Gujarat, India
                            </p></div>
                        </div>
                        <div class="mb-4 d-flex gap-2 ">
                            <div class="">
                            <i class="fas fa-phone-alt me-2 w-fit"></i>
                            </div>
                            <div class="">
                            <strong>Phone</strong></br>
                            <a href="tel:+919876543210" class="text-muted mb-0 a-tag-hover-color-red">
                                +91 98765 43210
                            </a>
                        </div>
                        </div>
                        <div class="mb-4 d-flex gap-2 ">
                            <div class="">
                            <i class="fas fa-envelope me-2 w-fit"></i>
                            </div>
                            <div class="">
                            <strong>Email Address</strong></br>
                            <a href="mailto:help@steveautoparts.com" class="text-muted mb-0 a-tag-hover-color-red">
                                help@steveautoparts.com
                            </a>
                        </div>
                        </div>
                        <div class="mb-4 d-flex gap-2 ">
                            <div class="">
                            <i class="fas fa-clock me-2 w-fit"></i>
                            </div>
                            <div class="">
                            <strong>Working Hours</strong>
                            <p class="text-muted mb-0">
                                Mon - Sat: 9:00 AM - 6:00 PM
                            </p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Google Map -->
        <div class="mt-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d20984356.956115294!2d-100.71302391942747!3d42.01410682907968!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2sin!4v1783516805589!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection