@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-images-edit-page', 'pageClass' => 'admin-images-edit-page'])
@section('page-title', 'Edit Image - ' . $image->original_name)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Image - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- Image Preview --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $image->thumb_url }}" alt="{{ $image->alt_text ?? $image->original_name }}" class="img-fluid admin-image-preview-large" onerror="this.onerror=null;this.src='{{ asset("assets/images/placeholder.png") }}'">
                    <hr>
                    <div class="table-responsive">
                    <table class="table table-sm table-borderless text-start small mb-0 responsive-table">
                        <tr><th class="text-muted width-25">Filename</th><td data-label="Filename">{{ $image->original_name }}</td></tr>
                        <tr><th class="text-muted width-25">URL</th><td class="text-break" data-label="URL"><a href="{{ $image->thumb_url }}" target="_blank"><code class="text-break">{{ $image->thumb_url }}</code></a></td></tr>
                        <tr><th class="text-muted width-25">MIME Type</th><td data-label="MIME Type">{{ $image->mime_type }}</td></tr>
                        <tr><th class="text-muted width-25">Size</th><td data-label="Size">{{ $image->size_in_kb }}</td></tr>
                        <tr><th class="text-muted width-25">Dimensions</th><td data-label="Dimensions">{{ $image->width && $image->height ? $image->width.' x '.$image->height.' px' : '—' }}</td></tr>
                        <tr>
                            <th class="text-muted align-top width-25">Attached To</th>
                            <td data-label="">
                                @forelse($usageLocations as $loc)
                                    <div class="mb-1">
                                        @if(!empty($loc['route']))
                                            <a href="{{ $loc['route'] }}" target="_blank" class="text-decoration-none">{{ $loc['type'] }} #{{ $loc['id'] }}</a>
                                        @else
                                            <span>{{ $loc['type'] }} #{{ $loc['id'] }}</span>
                                        @endif
                                        @if(!empty($loc['label']))
                                            <span class="text-muted width-25">({{ $loc['label'] }})</span>
                                        @endif
                                        @if(!empty($loc['usages']))
                                            <small class="text-success">— {{ implode(', ', $loc['usages']) }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-danger">Unused</span>
                                @endforelse
                            </td>
                        </tr>
                    </table>
                    </div>
                </div>
            </div>

            {{-- Convert --}}
            @if(in_array($image->mime_type, ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/gif']))
                @if($image->hasWebpVersion())
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body text-center">
                        <span class="badge bg-success fs-6 mb-2"><i class="fas fa-check-circle me-1"></i> Already Converted to WebP</span>
                        <p class="text-muted small mb-2">This image has already been converted.</p>
                        @php
                            $baseName = pathinfo($image->filename, PATHINFO_FILENAME);
                            $webpImage = \App\Models\Image::where('filename', $baseName . '.webp')->first();
                        @endphp
                        @if($webpImage)
                            <a href="{{ route('admin.images.edit', $webpImage->id) }}" class="btn btn-outline-success btn-sm steve-btn">
                                <i class="fas fa-eye"></i> View WebP Version
                            </a>
                        @endif
                    </div>
                </div>
                @else
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body text-center">
                        <form action="{{ route('admin.images.convert', $image->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 steve-btn gap-1">
                                <i class="fas fa-exchange-alt"></i> Convert to WebP
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            @endif
        </div>

        {{-- Edit Form --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Image Details</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.images.update', $image->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Alt Text <small class="text-muted">(for SEO & accessibility)</small></label>
                            <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text', $image->alt_text) }}" placeholder="Describe the image...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $image->title) }}" placeholder="Image title">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Caption</label>
                            <textarea name="caption" class="form-control texteditor" rows="3" placeholder="Optional caption">{{ old('caption', $image->caption) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary steve-btn gap-1">
                            <i class="fas fa-save"></i> Update Details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
