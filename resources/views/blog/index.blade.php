@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'blog-page', 'pageClass' => 'blog-page'])
@php $blogTitle = isset($category) ? $category->name : (isset($tag) ? 'Tag: ' . $tag->name : 'Blogs'); @endphp
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $blogTitle . ' - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => $blogTitle . ' | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => isset($category)
            ? ($category->description ?? 'Blogs about ' . $category->name)
            : (isset($tag)
                ? 'Blogs tagged with "' . $tag->name . '" from ' . config('app.name', 'StAutoparts') . '.'
                : 'Latest blogs, auto parts guides and tips from ' . config('app.name', 'StAutoparts') . '.'),
        'robots' => 'index, follow',
    ])
@endsection

@section('content')

<!-- Breadcrumb -->
<section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
  <div class="container">
    <div class="row justify-content-center content-wrapper">
      <div class="col-12">
        <h2 class="breadcrumb-title">{{ $blogTitle }}</h2>
        <ul class="bread-menu">
          <li><a href="{{ route('home') }}">Home</a></li>
          <li style="color: var(--primary)">Blog</li> 
        </ul>
      </div>
    </div>
  </div>
</section>

@if (!empty($page->content))
  <div class="container">
    <div class="page-content blog-cms-intro">
      {!! $page->content !!}
    </div>
  </div>
@endif

<!-- Blog Wrapper -->
<div class="gs-blog-wrapper">
  <div class="container">
    <div class="row flex-lg-row">
      <!-- Sidebar (first in DOM for mobile stacking) -->
      <div class="col-12 col-lg-4 mt-lg-0">
        <div class="blog-sidebar-overlay"></div>
        <div class="gs-blog-sidebar-wrapper">
          <!-- Close button inside sidebar -->
          <div class="blog-sidebar-close d-lg-none">
            <button type="button" class="bg-transparent p-2 steve-btn justify-content-end" aria-label="Close sidebar">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Search Widget -->
          <div class="single-blog-widget">
            @include('admin.partials.search-form', [
                'route' => route('blog'),
                'placeholder' => 'Search blogs...',
                'showClear' => !empty(request('search')) || isset($category) || isset($tag)
            ])
          </div>

          <!-- Categories Widget -->
          <div class="single-blog-widget">
            <h5 class="widget-title">Categories</h5>
            <ul class="cat-wrapper">
              @foreach($categories as $cat)
              <a href="{{ route('blog.category', $cat->slug) }}" class="blog-page-cat-list {{ isset($category) && $category->id === $cat->id ? 'active' : '' }}"><i class="fas fa-arrow-right ms-1"></i> {{ $cat->name }} ({{ $cat->blogs_count }})</a>
              @endforeach
            </ul>
          </div>

          <!-- Popular Tags Widget -->
          <!-- @if($tags->count())
          <div class="single-blog-widget popular-tags">
            <h5 class="widget-title">Popular Tags</h5>
            <ul class="cat-wrapper">
              @foreach($tags as $t)
              <a href="{{ route('blog.tag', $t->slug) }}" class="blog-page-cat-list {{ isset($tag) && $tag->id === $t->id ? 'active' : '' }}"><i class="fas fa-arrow-right ms-1"></i> {{ $t->name }} ({{ $t->blogs_count }})</a>
              @endforeach
            </ul>
          </div>
          @endif -->

          <!-- Clear Filters -->
          @if(!empty(request('search')) || isset($category) || isset($tag))
          <div class="single-blog-widget">
            <a href="{{ route('blog') }}" class="btn w-100 steve-btn fw-600 btn-primary">
              <i class="fas fa-times me-1"></i> Clear Filters
            </a>
          </div>
          @endif

          <!-- Recent Posts Widget -->
          <div class="single-blog-widget">
            <h5 class="widget-title">Recent Posts</h5>
            <div class="gs-sm-recent-post-wrapper">
              @foreach($recentBlogs->take(4) as $post)
              <a href="{{ route('blog.show', $post->slug) }}" class="recent-post d-flex">
                {!! imgTag(storedPath($post->image, 'assets/images/blogs', 'assets/images/blogs/placeholder.jpg'), '', '', 'width="80" height="80"') !!}
                <div class="recent-post-content">
                  <h6 class="post-title">{{ Str::limit($post->title, 50) }}</h6>
                  <span class="post-date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                      <path d="M8 2V4M16 2V4M3 10.5H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="#4c3533" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $post->post_date->format('M d - Y') }}
                  </span>
                </div>
              </a>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <!-- Main Blog Content -->
      <div class="col-12 col-lg-8 gs-main-blog-wrapper">
        @forelse($blogs as $blog)
        <div class="gs-main-single-blog">
          <div class="left-side-content">
            <a href="{{ route('blog.show', $blog->slug) }}">
              {!! imgTag(storedPath($blog->image, 'assets/images/blogs', 'assets/images/blogs/placeholder.jpg'), $blog->title, 'blog-img') !!}
            </a>
          </div>
          <div class="right-side-content">
            <div class="d-flex gap-1">
            @if($blog->category)
            <a href="{{ route('blog.category', $blog->category->slug) }}" class="blog-card-category">
              <i class="fas fa-folder me-1"></i> {{ $blog->category->name }}
            </a>
            @endif
            <div class="date-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none">
                <path d="M8 2V4M16 2V4M3 10.5H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="date-text">{{ $blog->post_date->format('M d - Y') }}</span>
            </div>
            </div>
            <h4>
              <a class="title" href="{{ route('blog.show', $blog->slug) }}">
                {{ Str::limit($blog->title, 70) }}
              </a>
            </h4>
            <p class="des pb-4">
              {{ Str::limit(strip_tags(html_entity_decode($blog->details ?? '')), 180) }}
            </p>
            <!-- @if($blog->tags->count())
            <div class="blog-card-tags">
              @foreach($blog->tags as $blogTag)
              <a href="{{ route('blog.tag', $blogTag->slug) }}" class="blog-card-tag">{{ $blogTag->name }}</a>
              @endforeach
            </div>
            @endif -->
            <a href="{{ route('blog.show', $blog->slug) }}" class="a-tag-hover-color">
              Read More
              <i class="fas fa-arrow-right ms-1"></i> 
            </a>  
          </div>
        </div>
        @empty
        <div class="text-center py-5">
          <p class="text-muted">No blog posts found.</p>
        </div>
        @endforelse

        <!-- Pagination -->
        @if($blogs->hasPages())
        <div class="d-flex justify-content-center mt-30">
          {{ $blogs->links('vendor.pagination.gs-pagination') }}
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Floating Sidebar Toggle (Mobile) -->
  <button class="blog-sidebar-toggle-float d-lg-none" type="button" aria-label="Toggle sidebar">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="4" y1="21" x2="4" y2="14"></line>
      <line x1="4" y1="10" x2="4" y2="3"></line>
      <line x1="12" y1="21" x2="12" y2="12"></line>
      <line x1="12" y1="8" x2="12" y2="3"></line>
      <line x1="20" y1="21" x2="20" y2="16"></line>
      <line x1="20" y1="12" x2="20" y2="3"></line>
      <line x1="1" y1="14" x2="7" y2="14"></line>
      <line x1="9" y1="8" x2="15" y2="8"></line>
      <line x1="17" y1="16" x2="23" y2="16"></line>
    </svg>
  </button>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var sidebar = document.querySelector('.gs-blog-sidebar-wrapper');
  var overlay = document.querySelector('.blog-sidebar-overlay');
  var toggle = document.querySelector('.blog-sidebar-toggle-float');
  var closeBtn = document.querySelector('.blog-sidebar-close button');

  function openSidebar() {
    sidebar.classList.add('active');
    overlay.classList.add('active');
    document.body.classList.add('overflow-hidden');
  }
  function closeSidebar() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    document.body.classList.remove('overflow-hidden');
  }

  if (toggle) toggle.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) closeSidebar();
  });
});
</script>
@endsection
