{{--
    Global breadcrumb section.

    Usage:
    @include('partials.breadcrumb', [
        'title' => 'All Brands',
        'items' => [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Brands'],
        ],
    ])

    Optional keys:
    - class:      extra classes for the section
    - titleClass: extra classes for the title
    - image:      background image URL (defaults to the standard breadcrumb image)
    - overlay:    set to true to render the FAQ hero overlay element
--}}
@php
    $breadcrumbSectionClass = trim('gs-breadcrumb-section ' . ($class ?? ''));
    $breadcrumbTitleClass = trim('breadcrumb-title ' . ($titleClass ?? ''));
    $breadcrumbImage = $image ?? asset('assets/images/1724480495Imagexxxxxpng.png');
    $breadcrumbItems = $items ?? [];
@endphp

<section class="{{ $breadcrumbSectionClass }}" style="background-image: url('{{ $breadcrumbImage }}'); background-size: cover; background-position: center;">
  @if (!empty($overlay))
    <div class="faq-hero-overlay"></div>
  @endif
  <div class="container">
    <div class="content-wrapper">
      <h2 class="{{ $breadcrumbTitleClass }}">{{ $title }}</h2>
      <ul class="bread-menu">
        @foreach ($breadcrumbItems as $item)
          <li>
            @if (!empty($item['url']))
              <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
              {{ $item['label'] }}
            @endif
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>
