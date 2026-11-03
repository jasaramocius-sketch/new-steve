@extends('admin.layouts.app')
@section('content')
<div class="card">
  <div class="card-header"><h5>Custom Settings</h5></div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.settings.custom.update') }}">
      @csrf
      <div class="form-group">
        <label>Tax Rate (%)</label>
        <input type="number" min="0" max="100" step="0.01" name="tax_rate" class="form-control" value="{{ $settings['tax_rate'] ?? 0 }}" required>
      </div>
      <div class="form-group">
        <label>Custom Value</label>
        <input type="text" name="custom_value" class="form-control" value="{{ $settings['custom_value'] ?? '' }}">
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
@endsection