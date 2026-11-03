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
                            <td class="text-right text-nowrap d-flex justify-content-end gap-1">
                                <a class="btn btn-outline-info steve-btn border-radius-50pr" href="{{ route('admin.settings.languages.translations', $language) }}" title="Translations" aria-label="Translations"><i class="fas fa-language"></i></a>
                                <a class="btn btn-outline-secondary steve-btn border-radius-50pr" href="{{ route('admin.settings.languages.export', $language) }}" title="Export ARB" aria-label="Export ARB"><i class="fas fa-download"></i></a>
                                <a class="btn btn-outline-info steve-btn border-radius-50pr" href="{{ route('admin.settings.languages.edit', $language) }}" title="Edit" aria-label="Edit"><i class="fas fa-edit"></i></a>
                                @if ($language->code !== 'en' && $defaultLanguage !== $language->code)
                                    <form action="{{ route('admin.settings.languages.destroy', $language) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this language?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-icon btn-circle btn-sm border-radius-50pr" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
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
