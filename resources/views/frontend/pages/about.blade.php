@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-about-page', 'pageClass' => 'user-about-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? ('About Us - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? ('About Us | ' . config('app.name', 'StAutoparts')),
        'metaDescription' => $page->meta_description ?? null,
        'robots' => 'index, follow',
    ])
@endsection
@section('content')
<!-- ABOUT PAGE STYLES -->
<style>
    /* Variables specific to the About content */
    

    /* Container for the page content */
    .about-content-wrapper {
        font-family: 'Inter', Helvetica, Arial, sans-serif;
        color: var(--text-dark);
        line-height: 1.6;
    }

    .about-container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Hero / Banner Area */
    .about-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.85)), url('https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&q=80&w=1600') no-repeat center center/cover;
        color: #ffffff;
        text-align: center;
        padding: 100px 0;
    }

    .about-hero h1 {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .about-hero p {
        font-size: 18px;
        max-width: 600px;
        margin: 0 auto;
        color: #dddddd;
    }

    /* Our Story Section */
    .about-story {
        padding: 80px 0;
    }

    .story-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
        align-items: center;
    }

    .story-text h2 {
        font-size: 36px;
        margin-bottom: 20px;
        color: var(--soft-dark);
        position: relative;
    }

    .story-text h2::after {
        content: '';
        display: block;
        width: 60px;
        height: 4px;
        background-color: var(--primary);
        margin-top: 10px;
    }

    .story-text p {
        margin-bottom: 15px;
        color: var(--text-muted);
    }

    .story-image img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: block;
    }

    /* Stats Section */
    .about-stats {
        background-color: var(--light-grey);
        padding: 60px 0;
        border-top: 1px solid #eeeeee;
        border-bottom: 1px solid #eeeeee;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        text-align: center;
    }

    .stat-box h3 {
        font-size: 36px;
        color: var(--primary);
        margin-bottom: 5px;
        font-weight: 700;
    }

    .stat-box p {
        font-size: 14px;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin: 0;
    }

    /* Core Values Section */
    .about-values {
        padding: 80px 0;
    }

    .values-title {
        text-align: center;
        font-size: 36px;
        margin-bottom: 50px;
        color: var(--soft-dark);
    }

    .values-title span {
        color: var(--primary);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .value-card {
        background: #ffffff;
        padding: 40px 30px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border-top: 3px solid transparent;
        transition: transform var(--transition-speed), border-color var(--transition-speed), box-shadow var(--transition-speed);
    }

    .value-card:hover {
        transform: translateY(-5px);
        border-top-color: var(--primary);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .value-icon {
        width: 70px;
        height: 70px;
        background-color: rgba(227, 27, 35, 0.1);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 28px;
    }

    .value-card h3 {
        font-size: 20px;
        margin-bottom: 15px;
        color: var(--soft-dark);
    }

    .value-card p {
        color: var(--text-muted);
        font-size: 15px;
        margin: 0;
    }

    /* Call to Action Section */
    .about-cta {
        background-color: var(--soft-dark);
        color: #ffffff;
        text-align: center;
        padding: 60px 0;
    }

    .about-cta h2 {
        font-size: 32px;
        margin-bottom: 15px;
    }

    .about-cta p {
        color: #cccccc;
        margin-bottom: 25px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    /* .btn-primary {
        display: inline-block;
        background-color: var(--primary);
        color: #ffffff;
        padding: 12px 30px;
        text-decoration: none;
        font-weight: 600;
        border-radius: 4px;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
        transition: background-color var(--transition-speed);
    } */
/* 
    .btn-primary:hover {
        background-color: #b5151b;
        color: #ffffff;
    } */

    /* Responsive Design Breakpoints */
    @media (max-width: 992px) {
        .story-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .values-grid {
            grid-template-columns: 1fr 1fr;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 70px 0;
        }
        .about-hero h1 {
            font-size: 38px;
        }
        .about-hero p {
            font-size: 16px;
        }
        .values-grid {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .value-card {
            padding: 30px 20px;
        }
    }

    @media (max-width: 576px) {
        .about-hero h1 {
            font-size: 34px;
        }
        .values-grid {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ABOUT PAGE CONTENT -->
<div class="about-content-wrapper">
    
    <!-- Hero / Banner Title Area -->
    <section class="about-hero">
        <div class="about-container">
            <h1>About Steve's Auto Parts</h1>
            <p>Your premier trusted source for high-quality, reliable new and aftermarket automotive parts.</p>
        </div>
    </section>

    <!-- Company Background / Story -->
    <section class="about-story">
        <div class="about-container story-grid">
            <div class="story-text">
                <h2>Our Story</h2>
                <p>For years, Steve's Auto Parts has proudly stood as a benchmark of reliability and elite service. What began as a local vision to deliver premium parts with honest advice has expanded into a comprehensive storefront, serving casual DIY mechanics, track enthusiasts, and local commercial garages alike.</p>
                <p>We specialize in sourcing high-quality aftermarket components, trusted replacement panels, lighting units, and essential engine gear. Every part that joins our catalog undergoes extensive processing to ensure your vehicle functions precisely at peak performance.</p>
                <p>We understand that vehicle maintenance can hit unexpectedly. That’s why our mission focuses on saving you time and money. Our new aftermarket and replacement choices are engineered to match OEM standards without matching steep dealership price tags.</p>
            </div>
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1517524206127-48bbd363f3d7?auto=format&fit=crop&q=80&w=800" alt="Steve's Auto Parts Workshop">
            </div>
        </div>
    </section>

    <!-- Operational Statistics -->
    <section class="about-stats">
        <div class="about-container stats-grid">
            <div class="stat-box">
                <h3><span class="counter" data-target="25">0</span>+</h3>
                <p>Years of Service</p>
            </div>
            <div class="stat-box">
                <h3><span class="counter" data-target="50">0</span>k+</h3>
                <p>Parts Monitored</p>
            </div>
            <div class="stat-box">
                <h3><span class="counter" data-target="15">0</span>k+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-box">
                <h3><span class="counter" data-target="100">0</span>%</h3>
                <p>Quality Checked</p>
            </div>
        </div>
    </section>

    <!-- Core Business Values -->
    <section class="about-values">
        <div class="about-container">
            <h2 class="values-title">Why Professionals Choose <span>Us</span></h2>
            <div class="values-grid">
                <!-- Point 1 -->
                <div class="value-card">
                    <div class="value-icon">
                        <!-- If you don't use FontAwesome, you can replace this with an SVG or image -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    </div>
                    <h3>Massive Inventory</h3>
                    <p>From custom mirror replacements and lights to internal drivetrain components, our warehouse stays stocked to service diverse domestic and import builds.</p>
                </div>
                <!-- Point 2 -->
                <div class="value-card">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <h3>Exceptional Value</h3>
                    <p>Avoid dealership markups. Our components maximize long-range reliability while protecting your bottom line and project budgets.</p>
                </div>
                <!-- Point 3 -->
                <div class="value-card">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    </div>
                    <h3>Expert Logistics</h3>
                    <p>We are parts professionals. If you need assistance matching rare engine parts or looking up precise component configurations, our experts are ready.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA Module -->
    <section class="about-cta">
        <div class="about-container">
            <h2>Tracking Down a Rare Component?</h2>
            <p>If a specialized asset isn't displaying inside our primary shelves, we will locate it immediately via our vast auto procurement network.</p>
            <a href="{{ route('contact') }}" class="btn-primary steve-btn steve-btn-hover">Submit a Parts Request</a>
        </div>
    </section>
    
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 50; // Lower number = faster counting

        // Function to handle the counting logic
        const startCounting = (counter) => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                
                // Calculate increment step based on total target and speed
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    // Call the function again after 15ms for smooth animation
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        };

        // Set up the Intersection Observer to watch for scroll
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5 // Triggers when 50% of the section is visible
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Find counters within the visible section and animate them
                    const countersInView = entry.target.querySelectorAll('.counter');
                    countersInView.forEach(counter => {
                        startCounting(counter);
                    });
                    // Stop observing once the animation has run
                    observer.unobserve(entry.target); 
                }
            });
        }, observerOptions);

        // Target the stats section to watch
        const statsSection = document.querySelector('.about-stats');
        if (statsSection) {
            observer.observe(statsSection);
        }
    });
</script>
@endsection
