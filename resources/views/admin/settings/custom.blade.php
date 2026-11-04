@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-Tax-Rate-settings-page', 'pageClass' => 'admin-Tax-Rate-settings-page'])
@section('page-title', 'Tax Rate and Custom Value Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Tax Rate and Custom Value Settings',
    ])
@endsection
@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.settings.custom.update') }}">
      @csrf
      <div class="form-row d-flex flex-column gap-3 mb-2">
        <div class="form-group">
          <label class="form-label mt-0">Tax Rate (%)</label>
          <input type="number" min="0" max="100" step="0.01" name="tax_rate" class="form-control" value="{{ $settings['tax_rate'] ?? 0 }}" required>
        </div>
        <div class="form-group">
          <label class="form-label mt-0">Custom Value</label>
          <input type="text" name="custom_value" class="form-control" value="{{ $settings['custom_value'] ?? '' }}">
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
@endsection