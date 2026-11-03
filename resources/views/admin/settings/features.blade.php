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
                                <form class="feature-setting-form" data-endpoint="{{ route('admin.settings.features.update') }}">
                                    @csrf
                                    <input type="hidden" name="key" value="{{ $item['key'] }}">
                                    <label class="feature-switch mb-0">
                                        <input type="checkbox" name="enabled" value="1" aria-label="{{ $item['label'] }}" @checked($item['enabled'])>
                                        <span class="feature-switch-track" aria-hidden="true"></span>
                                    </label>
                                    <p class="feature-setting-status small mb-0 mt-2" role="status" aria-live="polite"></p>
                                </form>

                                @if (!empty($item['description']))
                                    <div class="alert alert-info feature-setting-note mb-0 mt-3">{{ $item['description'] }}</div>
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

    .feature-switch {
        position: relative;
        display: inline-flex;
        width: 48px;
        height: 26px;
        cursor: pointer;
    }

    .feature-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .feature-switch-track {
        position: absolute;
        inset: 0;
        background: #b7c0bd;
        border-radius: 26px;
        transition: background-color .18s ease;
    }

    .feature-switch-track::before {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        content: "";
        background: #fff;
        border-radius: 50%;
        transition: transform .18s ease;
    }

    .feature-switch input:checked + .feature-switch-track {
        background: #267c70;
    }

    .feature-switch input:checked + .feature-switch-track::before {
        transform: translateX(22px);
    }

    .feature-switch input:focus-visible + .feature-switch-track {
        outline: 3px solid rgba(38, 124, 112, .3);
        outline-offset: 2px;
    }

    .feature-switch input:disabled + .feature-switch-track {
        cursor: wait;
        opacity: .65;
    }

    .feature-setting-note {
        color: #15536b;
        font-size: .875rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .feature-switch-track,
        .feature-switch-track::before {
            transition: none;
        }
    }
</style>

<script>
document.querySelectorAll('.feature-setting-form').forEach((form) => {
    const checkbox = form.querySelector('input[name="enabled"]');
    const status = form.querySelector('.feature-setting-status');

    checkbox.addEventListener('change', async () => {
        const previousValue = !checkbox.checked;
        checkbox.disabled = true;
        status.textContent = 'Saving...';
        status.classList.remove('text-danger', 'text-success');

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

            if (!response.ok) {
                throw new Error('Could not save this setting.');
            }

            status.textContent = 'Saved';
            status.classList.add('text-success');
        } catch (error) {
            checkbox.checked = previousValue;
            status.textContent = error.message || 'Could not save this setting.';
            status.classList.add('text-danger');
        } finally {
            checkbox.disabled = false;
        }
    });
});
</script>
@endsection