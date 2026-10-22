@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-faqs-create-page', 'pageClass' => 'admin-faqs-create-page'])
@section('page-title', 'Add FAQ')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add FAQ - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <!-- <div class="card-header bg-white">
        <h5 class="mb-0">Add FAQ</h5>
    </div> -->
    <div class="card-body">
        <form action="{{ route('admin.faqs.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Question *</label>
                    <input type="text" name="question" class="form-control @error('question') is-invalid @enderror" value="{{ old('question') }}" required>
                    @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}" min="0">
                    @error('order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" checked>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Answer *</label>
                    <textarea name="answer" class="form-control texteditor @error('answer') is-invalid @enderror" rows="6" required>{{ old('answer') }}</textarea>
                    @error('answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary steve-btn gap-1"><i class="fas fa-save"></i> Save</button>
                <a href="{{ route('admin.faqs.index') }}" class="btn btn-primary gap-1">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
