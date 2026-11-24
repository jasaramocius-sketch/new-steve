@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-terms-conditions-page', 'pageClass' => 'user-terms-conditions-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? ('Terms & Conditions - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? ('Terms & Conditions | ' . config('app.name', 'StAutoparts')),
        'metaDescription' => $page->meta_description ?? null,
        'robots' => 'index, follow',
    ])
@endsection
@section('content')

  <style>
    :root {
      --primary: #d92323;
      --dark-bg: #1a1c1e;
      --secondary-bg: #222529;
      --light-bg: #f4f6f8;
      --text-dark: #2d3139;
      --border-color: #e2e8f0;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --radius: 8px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: var(--text-dark); background-color: var(--light-bg); line-height: 1.6; }

    header { background-color: var(--dark-bg); color: #fff; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; }
    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
    .brand-logo { background-color: var(--primary); color: #fff; font-weight: 900; font-size: 1.4rem; padding: 4px 10px; border-radius: 4px; }
    .brand-title { font-size: 1.3rem; font-weight: 700; }

    .hero { background: linear-gradient(135deg, var(--dark-bg) 0%, var(--secondary-bg) 100%); color: #fff; padding: 2.5rem 5%; text-align: center; border-bottom: 4px solid var(--primary); }

    .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; display: grid; grid-template-columns: 280px 1fr; gap: 2rem; }
    .sidebar { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1.25rem; height: fit-content; }
    .sidebar h3 { font-size: 0.9rem; text-transform: uppercase; color: #6c757d; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); }
    .nav-list { list-style: none; }
    .nav-list a { display: block; padding: 0.75rem 1rem; color: var(--text-dark); text-decoration: none; font-weight: 600; border-radius: 6px; margin-bottom: 0.5rem; }
    .nav-list a.active, .nav-list a:hover { background-color: #fef2f2; color: var(--primary); }
    .nav-list a.active { border-left: 4px solid var(--primary); border-top-left-radius: 0; border-bottom-left-radius: 0; }

    .content { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); padding: 2.5rem; }
    .content h2 { font-size: 1.8rem; color: var(--dark-bg); margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color); }
    .content h3 { font-size: 1.2rem; margin: 1.5rem 0 0.5rem; }
    .content p, .content ul { margin-bottom: 1rem; color: #4a5568; }
    .content ul { padding-left: 1.5rem; }

    .callout { background-color: #fff8f6; border-left: 4px solid var(--primary); padding: 1rem; margin: 1.5rem 0; border-radius: 0 var(--radius) var(--radius) 0; }

    footer { background-color: var(--dark-bg); color: #a0aec0; text-align: center; padding: 2rem 1rem; margin-top: 4rem; font-size: 0.9rem; }

    @media (max-width: 868px) {
      .container { grid-template-columns: 1fr; }
      .nav-list { display: flex; overflow-x: auto; gap: 0.5rem; }
      .nav-list a { white-space: nowrap; }
    }
  </style>
  <section class="hero">
    <h1>Terms & Conditions</h1>
    <p>ST Auto Parts - Rules, terms, and service guidelines.</p>
  </section>

  <div class="container">
    <aside class="sidebar">
      <h3>Policies Nav</h3>
      <ul class="nav-list">
        <li><a href="terms.html" class="active">Terms & Conditions</a></li>
        <li><a href="privacy.html">Privacy Policy</a></li>
        <li><a href="return.html">Return Policy</a></li>
        <li><a href="support.html">Support Policy</a></li>
      </ul>
    </aside>

    <main class="content">
      <h2>Terms & Conditions</h2>
      <p>Welcome to <strong>ST Auto Parts</strong>. By accessing or shopping on our website, you agree to comply with and be bound by the following terms and conditions.</p>
      
      <h3>1. Vehicle Fitment & Compatibility</h3>
      <p>While we provide vehicle lookup tools and VIN compatibility checks, <strong>it is the customer's ultimate responsibility to verify component fitment</strong> prior to installation. Always cross-check OEM part numbers or consult a certified mechanic.</p>

      <h3>2. Professional Installation</h3>
      <p>Automotive parts carry inherent safety risks. ST Auto Parts is not responsible for labor costs, damages, or injuries caused by improper installation. We recommend installation by an ASE-certified technician.</p>

      <div class="callout">
        <strong>Note for Race & Performance Parts:</strong> Certain performance items are designed exclusively for off-road or track use and may not be emissions-compliant in your area.
      </div>

      <h3>3. Pricing & Typographical Errors</h3>
      <p>We reserve the right to correct any pricing errors or cancel orders resulting from system glitches or inaccurate inventory details.</p>
    </main>
  </div>
@endsection
