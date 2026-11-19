@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'blog-show-page', 'pageClass' => 'blog-show-page'])
@php $blogDesc = trim(strip_tags(html_entity_decode($blog->details ?? ''))); $blogDesc = mb_strlen($blogDesc) > 160 ? mb_substr($blogDesc, 0, 157) . '...' : $blogDesc; @endphp
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $blog->title . ' - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => $blog->title . ' | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => $blogDesc ?: null,
        'ogImage' => $blog->image ?? null,
        'ogImagePrefix' => 'assets/images/blogs',
        'robots' => 'index, follow',
    ])
@endsection

@section('content')

<!-- Breadcrumb -->
@include('partials.breadcrumb', [
    'title' => 'Blog Details',
    'items' => [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Blog', 'url' => route('blog')],
        ['label' => Str::limit($blog->title, 40)],
    ],
])

<!-- Blog Wrapper -->
<div class="gs-blog-wrapper">
  <div class="container">
    <a href="{{ route('blog') }}" class="btn btn-outline-primary single-blog-button steve-btn w-auto mb-2">
      <i class="fas fa-arrow-left"></i>
        Back to Blog
    </a>
    <div class="row">      
      <!-- Main Content -->
      <div class="col-12 col-lg-8 gs-main-blog-wrapper">
        <div class="gs-blog-details-wrapper">
          <div class="gs-blog-card">

            <!-- Featured Image -->
            {!! imgTag(storedPath($blog->image, 'assets/images/blogs', 'assets/images/blogs/placeholder.jpg'), $blog->title, 'fea-img img-fluid') !!}

            <!-- Meta Info -->
            <div class="meta-info-wrapper">
              <div class="single-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="meta-title">By: {{ $blog->author ?: 'Admin' }}</span>
              </div>
              <div class="single-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M8 2V4M16 2V4M3 10.5H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="meta-title">{{ $blog->post_date->format('M d - Y') }}</span>
              </div>
              @foreach($blog->allCategories() as $blogCategory)
              <div class="single-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <a href="{{ route('blog.category', $blogCategory->slug) }}" class="meta-title">{{ $blogCategory->name }}</a>
              </div>
              @endforeach
            </div>

            <!-- Post Title -->
            <h4 class="fea-title mb-24">{{ $blog->title }}</h4>

            <!-- Post Content -->
            <div class="blog-content-body">
              {!! $blog->details !!}
            </div>

            @if($blog->tags->count())
            <div class="blog-details-tags">
              <span class="tags-label">Tags:</span>
              @foreach($blog->tags as $blogTag)
              <a href="{{ route('blog.tag', $blogTag->slug) }}" class="blog-details-tag">{{ $blogTag->name }}</a>
              @endforeach
            </div>
            @endif

          </div>
        </div>

        <x-page-blocks :model="$blog" />

        <nav class="single-blog-content-navigation" aria-label="Blog post navigation">
          <div class="single-blog-navigation-item">
            @if($previous)
              <a class="single-blog-navigation-link" href="{{ route('blog.show', $previous->slug) }}">
                <span class="single-blog-navigation-direction">
                  <i class="fas fa-arrow-left" aria-hidden="true"></i>
                  Previous Post
                </span>
                <span class="single-blog-navigation-title">{{ $previous->title }}</span>
              </a>
            @else
              <div class="single-blog-navigation-link is-disabled">
                <span class="single-blog-navigation-direction">
                  <i class="fas fa-arrow-left" aria-hidden="true"></i>
                  Previous Post
                </span>
                <span class="single-blog-navigation-title">No previous post</span>
              </div>
            @endif
          </div>
          <div class="single-blog-navigation-item is-next">
            @if($next)
              <a class="single-blog-navigation-link" href="{{ route('blog.show', $next->slug) }}">
                <span class="single-blog-navigation-direction">
                  Next Post
                  <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </span>
                <span class="single-blog-navigation-title">{{ $next->title }}</span>
              </a>
            @else
              <div class="single-blog-navigation-link is-disabled">
                <span class="single-blog-navigation-direction">
                  Next Post
                  <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </span>
                <span class="single-blog-navigation-title">No next post</span>
              </div>
            @endif
          </div>
        </nav>
      </div>

      <!-- Sidebar -->
      <div class="col-12 col-lg-4 mt-40 mt-lg-0">
        <aside class="gs-blog-sidebar-wrapper right-side" role="complementary" aria-label="Blog sidebar">
          <!-- Search Widget -->
          <!-- <div class="single-blog-widget">
            <h5 class="widget-title">Search</h5>
            <form class="search-form" action="{{ route('blog') }}" method="GET">
              <input class="input-box" type="text" name="search" placeholder="Find anything...">
              <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </form>
          </div> -->

          <!-- Categories Widget -->
          <!-- <div class="single-blog-widget">
            <h5 class="widget-title">Categories</h5>
            <ul class="cat-wrapper">
              @foreach($categories as $cat)
              <li><a href="{{ route('blog.category', $cat->slug) }}">{{ $cat->name }} ({{ $cat->total_blogs_count }})</a></li>
              @endforeach
            </ul>
          </div> -->

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

        </aside>
      </div>

    </div>
  </div>
</div>
@endsection
