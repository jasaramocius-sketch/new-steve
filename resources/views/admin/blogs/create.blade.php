@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-blog-create-page', 'pageClass' => 'admin-blog-create-page'])
@section('page-title', 'Add Blog')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add Blog - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                {{-- =========================
                    LEFT CONTENT
                ========================== --}}
                <div class="col-lg-8">
                    {{-- Title --}}
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text"
                            name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}"
                            required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Content --}}
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea id="editor"
                            name="details"
                            class="form-control texteditor @error('details') is-invalid @enderror"
                            rows="15">{{ old('details') }}</textarea>
                        @error('details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                {{-- =========================
                    RIGHT SIDEBAR
                ========================== --}}
                <div class="col-lg-4">
                    <div class="blog-sidebar">
                        {{-- Status --}}
                        <div class="card border shadow-none mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Publish</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status"
                                        class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft"
                                            {{ old('status') === 'draft' ? 'selected' : '' }}>
                                            Draft
                                        </option>
                                        <option value="scheduled"
                                            {{ old('status') === 'scheduled' ? 'selected' : '' }}>
                                            Scheduled
                                        </option>
                                        <option value="published"
                                            {{ old('status') === 'published' ? 'selected' : '' }}>
                                            Published
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Post Date --}}
                                <div class="mb-0">
                                    <label class="form-label">
                                        Post Date
                                        <small class="text-muted">(optional)</small>
                                    </label>
                                    <input type="datetime-local"
                                        name="published_at"
                                        class="form-control @error('published_at') is-invalid @enderror"
                                        value="{{ old('published_at') }}">
                                    @error('published_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Required when status is Scheduled.
                                        Empty + Published = visible now.
                                    </small>
                                </div>
                                {{-- Actions --}}
                                <div class="d-flex gap-2 pt-3">
                                    <button type="submit"
                                        class="btn btn-primary steve-btn gap-1">
                                        <i class="fas fa-save"></i>
                                        Save
                                    </button>
                                    <a href="{{ route('admin.blogs.index') }}"
                                        class="btn btn-primary steve-btn gap-1">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                        {{-- Category --}}
                        <div class="card border shadow-none mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Category</h6>
                            </div>
                            <div class="card-body">
                    @include('admin.partials.blog-category-field', [
                        'selectedCategoryId' => old('blog_category_id'),
                        'selectedAdditionalIds' => old('additional_categories', [])
                    ])
                            </div>
                        </div>
                        {{-- Tags --}}
                        <div class="card border shadow-none mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Tags</h6>
                            </div>
                            <div class="card-body">
                                @include('admin.partials.blog-tags-input', [
                                    'selectedTags' => old('tags', ''),
                                    'allTags' => $allTagsString
                                ])
                            </div>
                        </div>
                        {{-- Featured Image --}}
                        <div class="card border shadow-none mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Featured Image</h6>
                            </div>
                            <div class="card-body">
                                <input type="hidden"
                                    name="image_from_manager"
                                    id="image_from_manager_blog_image">
                                <div id="impPreview_blog_image"
                                    class="d-none mb-2">
                                </div>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="impOpen_blog_image()">
                                    <i class="fas fa-images me-1"></i>
                                    Browse Image Manager
                                </button>
                                @error('image')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        {{-- Image URL --}}
                        <div class="card border shadow-none mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Image URL</h6>
                            </div>
                            <div class="card-body">
                                <input type="url"
                                    name="image_url"
                                    class="form-control @error('image_url') is-invalid @enderror"
                                    value="{{ old('image_url') }}"
                                    placeholder="https://example.com/image.jpg">
                                @error('image_url')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">
                                    Provide a URL only if no image is selected.
                                </small>
                            </div>
                        </div>             
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'blog_image', 'targetInput' => 'image_from_manager'])

@endsection
