@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-email-templates-page', 'pageClass' => 'admin-email-templates-page'])
@section('page-title', 'Email Templates')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Email Templates'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transactional Email Templates</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $template->name }}</div>
                                <div class="text-muted small">{{ $template->description }}</div>
                                <code class="small">{{ $template->key }}</code>
                            </td>
                            <td>{{ $template->subject }}</td>
                            <td>
                                @if($template->is_enabled)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.settings.email-templates.edit', $template->key) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
