@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-features-page', 'pageClass' => 'admin-features-page'])
@section('page-title', 'Features activation')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Features activation',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px ap-features-page">
    <div class="alert alert-info">All activation toggles are listed below. Items marked “Integration required” are saved but do not appear as actionable storefront options until their provider flow is implemented.</div>

    <!-- <div class="ap-page-header mb-4">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Features activation</span>
        </nav>
        <div class="ap-page-header-row">
            <h1 class="ap-page-title">Features activation</h1>
        </div>
    </div> -->

    @foreach ($settingsGroups as $group)
        <section class="mb-4">
            <h2 class="h5 text-center text-muted mb-3">{{ $group['title'] }}</h2>
            <div class="row">
                @foreach ($group['items'] as $item)
                    <div class="col-lg-4 col-md-6 mb-3">
                        <article class="card h-100 feature-setting-card">
                            <div class="card-header">
                                <h3 class="mb-0 h6 text-center">{{ $item['label'] }}</h3>
                            </div>
                            <div class="card-body text-center">
                                @if (!empty($item['always_on']))
                                    <div class="form-check form-switch d-inline-block mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" checked disabled aria-label="{{ $item['label'] }} (always enabled)" title="Always enabled for new customer accounts">
                                    </div>
                                @else
                                    <form class="feature-setting-form" data-endpoint="{{ route('admin.settings.features.update') }}">
                                        @csrf
                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                        <div class="form-check form-switch d-inline-block mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" aria-label="{{ $item['label'] }}" @checked($item['enabled'])>
                                        </div>
                                    </form>
                                @endif

                                @if (!empty($item['description']))
                                    <div class="alert alert-info feature-setting-note mb-0 mt-3">{{ $item['description'] }}</div>
                                @endif
                                @if (!empty($item['integration_required']))
                                    <div class="alert alert-warning feature-setting-note mb-0 mt-3">
                                        <strong>Integration required.</strong> This toggle is saved, but the provider flow is not connected yet, so no storefront option is shown.
                                    </div>
                                @endif
                                @if (!empty($item['configure']))
                                    <p class="small mb-0 mt-3">You need to configure this service before enabling it.
                                        <a href="{{ route($item['configure']) }}">{{ $item['configureLabel'] ?? 'Configure now' }}</a>
                                    </p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>

<style>
    .feature-setting-card {
        border-radius: 5px;
    }

    .feature-setting-note {
        color: #15536b;
        font-size: .875rem;
    }
</style>

<script>
document.querySelectorAll('.feature-setting-form').forEach((form) => {
    const checkbox = form.querySelector('input[name="enabled"]');

    checkbox.addEventListener('change', async () => {
        const previousValue = !checkbox.checked;
        checkbox.disabled = true;

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    key: form.querySelector('input[name="key"]').value,
                    enabled: checkbox.checked,
                }),
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Could not save this setting.');
            }

            if (window.toastr) {
                toastr.success(result.message || 'Feature setting saved successfully.');
            }
        } catch (error) {
            checkbox.checked = previousValue;
            if (window.toastr) {
                toastr.error(error.message || 'Could not save this setting.');
            }
        } finally {
            checkbox.disabled = false;
        }
    });
});
</script>
@endsection