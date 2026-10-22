@extends('layouts.app'){{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-faq-page', 'pageClass' => 'user-faq-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? ('FAQ - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? ('FAQ | ' . config('app.name', 'StAutoparts')),
        'metaDescription' => $page->meta_description ?? 'Frequently asked questions about ST Auto Parts — orders, shipping, returns and support.',
        'robots' => 'index, follow',
    ])
@endsection
@section('content')

@php
    // Build the category chip list only if the faqs actually carry a category value.
    $faqCategories = $faqs->pluck('category')->filter()->unique()->values();
@endphp

<div class="Faq-hero">
<!-- Banner Hero Section -->
<section class="gs-breadcrumb-section faq-hero-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
  <div class="faq-hero-overlay"></div>
  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title faq-hero-title">Frequently asked questions</h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li style="color: var(--primary)">FAQs</li>
      </ul>
    </div>
  </div>
</section>
</div>

<div class="Faqs-page-container">
  <div class="container-fluid px-3 px-md-4">
    <div class="row justify-content-center Faqs-page-row">
      <div class="col-12 col-lg-8 Faqs-page-col">

        <!-- <div class="faq-search-wrap">
          <i class="fas fa-search faq-search-icon"></i>
          <input type="text" id="faqSearch" class="form-control faq-search-input" placeholder="Search questions">
        </div> -->

        @if($faqCategories->isNotEmpty())
        <div class="faq-chip-row" id="faqChipRow">
          <button type="button" class="faq-chip is-active" data-cat="all">All</button>
          @foreach($faqCategories as $category)
          <button type="button" class="faq-chip" data-cat="{{ Str::slug($category) }}">{{ $category }}</button>
          @endforeach
        </div>
        @endif

        <div id="faqsAccordion" class="faq-list">
          @forelse($faqs as $index => $faq)
          <div class="faq-item"
               data-cat="{{ $faq->category ?? '' ? Str::slug($faq->category) : 'all' }}"
               data-q="{{ Str::lower($faq->question) }}">
            <button type="button" class="faq-trigger" aria-expanded="false" aria-controls="faq{{ $faq->id }}">
                <span class="faq-code">Q-{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="faq-question">{{ $faq->question }}</span>
                <i class="fas fa-plus faq-icon"></i>
            </button>
            <div id="faq{{ $faq->id }}" class="faq-answer">
                <div class="mb-0">{!! $faq->answer !!}</div>
            </div>
          </div>
          @empty
          <div class="bg-white rounded shadow-sm p-5 text-center">
            <i class="fas fa-info-circle text-muted mb-3" style="font-size:3rem"></i>
            <h3>No FAQs available</h3>
            <p class="text-muted">Check back later for frequently asked questions.</p>
          </div>
          @endforelse
        </div>

        <div id="faqNoResults" class="faq-no-results" style="display:none;">
          <p class="text-muted mb-0">No questions match your search.</p>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<style>
.faq-hero-section {
    position: relative;
    overflow: hidden;
}
.faq-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(27,31,36,0.75), rgba(27,31,36,0.55));
}
.faq-hero-section .content-wrapper {
    position: relative;
    z-index: 1;
}
.faq-hero-title {
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #fff;
}

.faq-search-wrap {
    position: relative;
    margin-bottom: 16px;
}
.faq-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9BA0A8;
    font-size: 14px;
}
.faq-search-input {
    padding-left: 38px;
    height: 44px;
    border-radius: 8px;
    width: 100%;
}

.faq-chip-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.faq-chip {
    border: 1px solid #dcdfe3;
    background: transparent;
    color: #5a5f66;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    line-height: 1.4;
    white-space: nowrap;
}
.faq-chip.is-active {
    background: #1D4E89;
    border-color: #1D4E89;
    color: #fff;
}

.faq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.faq-item {
    background: #fff;
    border: 1px solid #e9eaec;
    border-radius: 8px;
    overflow: hidden;
}
.faq-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
}
.faq-code {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    font-weight: 700;
    color: #1D4E89;
    background: #e6f0fa;
    padding: 2px 7px;
    border-radius: 4px;
    flex-shrink: 0;
}
.faq-question {
    flex: 1;
    font-weight: 600;
    font-size: 14px;
    color: #1B1F24;
    min-width: 0;
    word-break: break-word;
}
.faq-icon {
    color: #9BA0A8;
    font-size: 13px;
    flex-shrink: 0;
}
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
    padding: 0 16px 0 58px;
    font-size: 13.5px;
    color: #5a5f66;
    line-height: 1.6;
}
.faq-answer.is-open {
    padding: 0 16px 0px 68px;
}
.faq-answer.is-open .faq-answer p {
    padding-bottom: 12px;
}
.faq-answer p {
    padding-bottom: 12px;
}
.faq-no-results {
    text-align: center;
    padding: 32px;
}

/* Responsive breakpoints */
@media (max-width: 768px) {
    .faq-hero-title {
        font-size: 22px;
    }
    .faq-code {
        font-size: 10px;
        padding: 2px 6px;
    }
    .faq-question {
        font-size: 13px;
    }
}

@media (max-width: 576px) {
    .faq-hero-section {
        padding: 24px 0;
    }
    .faq-search-input {
        height: 40px;
        font-size: 13px;
    }
    .faq-chip {
        padding: 5px 12px;
        font-size: 12px;
    }
    .faq-trigger {
        padding: 12px;
        gap: 8px;
    }
    .faq-code {
        display: none;
    }
    .faq-answer,
    .faq-answer.is-open {
        padding-left: 12px;
        padding-right: 12px;
    }
}
</style>

<script>
(function() {
    var triggers = document.querySelectorAll('.faq-trigger');

    function closeItem(trigger) {
        var answer = trigger.parentElement.querySelector('.faq-answer');
        var icon = trigger.querySelector('.faq-icon');
        trigger.setAttribute('aria-expanded', 'false');
        answer.classList.remove('is-open');
        answer.style.maxHeight = null;
        icon.classList.remove('fa-minus');
        icon.classList.add('fa-plus');
    }

    function openItem(trigger) {
        var answer = trigger.parentElement.querySelector('.faq-answer');
        var icon = trigger.querySelector('.faq-icon');
        trigger.setAttribute('aria-expanded', 'true');
        answer.classList.add('is-open');
        answer.style.maxHeight = answer.scrollHeight + 'px';
        icon.classList.remove('fa-plus');
        icon.classList.add('fa-minus');
    }

    triggers.forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            var expanded = trigger.getAttribute('aria-expanded') === 'true';

            // Auto-close every other item first, then toggle this one.
            triggers.forEach(function(t) {
                if (t !== trigger) closeItem(t);
            });

            if (expanded) {
                closeItem(trigger);
            } else {
                openItem(trigger);
            }
        });
    });

    // Open the first FAQ by default when the page loads.
    if (triggers.length > 0) {
        openItem(triggers[0]);
    }

    // Recalculate the open answer's height on resize (e.g. rotating a phone,
    // or text reflowing to more/fewer lines at a new width).
    window.addEventListener('resize', function() {
        var openTrigger = document.querySelector('.faq-trigger[aria-expanded="true"]');
        if (openTrigger) {
            var answer = openTrigger.parentElement.querySelector('.faq-answer');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }
    });

    var chips = document.querySelectorAll('.faq-chip');
    var searchInput = document.getElementById('faqSearch');
    var items = document.querySelectorAll('.faq-item');
    var noResults = document.getElementById('faqNoResults');

    function activeCategory() {
        var active = document.querySelector('.faq-chip.is-active');
        return active ? active.dataset.cat : 'all';
    }

    function filterFaqs() {
        var cat = activeCategory();
        var query = (searchInput.value || '').toLowerCase().trim();
        var visibleCount = 0;

        items.forEach(function(item) {
            var matchesCat = cat === 'all' || item.dataset.cat === cat;
            var matchesQuery = query === '' || item.dataset.q.indexOf(query) !== -1;
            var show = matchesCat && matchesQuery;
            item.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            filterFaqs();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterFaqs);
    }
})();
</script>
@endsection