@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-languages-page', 'pageClass' => 'admin-languages-page'])
@section('page-title', 'Default Language')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Default Language',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="row mb-3">
        <div class="ap-page-actions mb-4"><a href="{{ route('admin.settings.languages.create') }}" class="btn btn-info">Add New Language</a></div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Default Language</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.languages.default') }}" method="POST" class="form-inline">
                        @csrf
                        <label for="default-language" class="sr-only">Default Language</label>
                        <div class=" d-grid gap-2 gap-md-3 flex-wrap flex-md-nowrap align-items-end grid-template-columns">
                        <select id="default-language" name="default_language" class="form-control form-select mr-2" required>
                            @foreach ($activeLanguages as $language)
                                <option value="{{ $language->code }}" @selected($defaultLanguage === $language->code)>{{ $language->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-info">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Import App Translations</h2></div>
                <div class="card-body">
                    <form id="language-import-form" action="{{ $activeLanguages->isNotEmpty() ? route('admin.settings.languages.import', $activeLanguages->first()) : '#' }}" method="POST" enctype="multipart/form-data" class="">
                        <div class="d-flex flex-wrap align-items-end">
                        @csrf
                        <div class="form-group mb-2 mr-2 flex-grow-1">
                            <label for="language-import-target">Import into language</label>
                            <select id="language-import-target" class="form-control form-select" @disabled($activeLanguages->isEmpty())>
                                @foreach ($activeLanguages as $language)
                                    <option value="{{ route('admin.settings.languages.import', $language) }}">{{ $language->name }}</option>
                                @endforeach
                            </select>
                            <input id="lang-file" type="file" name="lang_file" class="form-control-file mt-2" accept=".arb,application/json" required>
                        </div>
                        </div>
                        <button type="submit" class="btn btn-info mb-2" @disabled($activeLanguages->isEmpty())>Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="ap-page-header mb-3">
        <!-- <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Language</span>
        </nav> -->
        <div class="ap-page-header-row">
            <!-- <h1 class="ap-page-title">Language</h1> -->            
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.settings.languages.index') }}" method="GET" class="w-100">
                <div class="row gutters-5 align-items-end">
                    <div class="col-lg-3 col-md-4 col-8 mb-2">
                        <label for="language-search" class="fs-12 text-muted mb-1">Search</label>
                        <input id="language-search" type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Name or code">
                    </div>
                    <div class="col-auto mb-2"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Name</th><th>Code</th><th>Flutter App Lang Code</th><th>RTL</th><th>Status</th><th class="text-right">Options</th></tr></thead>
                <tbody>
                    @forelse ($languages as $language)
                        <tr>
                            <td>{{ $languages->firstItem() + $loop->index }}</td>
                            <td>{{ $language->name }}</td>
                            <td>{{ $language->code }}</td>
                            <td>{{ $language->flutter_code ?: '-' }}</td>
                            <td>
                                <form action="{{ route('admin.settings.languages.rtl', $language) }}" method="POST" class="language-toggle-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $language->rtl ? 'btn-success' : 'btn-outline-secondary' }}" aria-label="Toggle RTL for {{ $language->name }}">{{ $language->rtl ? 'RTL' : 'LTR' }}</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.settings.languages.status', $language) }}" method="POST" class="language-toggle-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $language->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $language->is_active ? 'Active' : 'Inactive' }}</button>
                                </form>
                            </td>
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.settings.languages.translations', $language) }}" class="action-btn btn-view-history" data-bs-toggle="tooltip" data-bs-placement="top" title="Translations" aria-label="Translations">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="M22 22l-5-10-5 10"/><path d="M14 18h6"/></svg>
                                    </a>
                                    <a href="{{ route('admin.settings.languages.export', $language) }}" class="action-btn btn-restore" data-bs-toggle="tooltip" data-bs-placement="top" title="Export ARB" aria-label="Export ARB">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                                    </a>
                                    <a href="{{ route('admin.settings.languages.edit', $language) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" aria-label="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    @if ($language->code !== 'en' && $defaultLanguage !== $language->code)
                                        <form action="{{ route('admin.settings.languages.destroy', $language) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this language?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" aria-label="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No languages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $languages->links() }}</div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.language-toggle-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: new FormData(form),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Could not update language.');
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
            button.disabled = false;
        }
    });
});

const importTarget = document.getElementById('language-import-target');
const importForm = document.getElementById('language-import-form');
if (importTarget && importForm) importForm.action = importTarget.value;
</script>
@endsection
