@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-settings-page', 'pageClass' => 'admin-settings-page'])
@section('page-title', 'All Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Settings',
    ])
@endsection
@section('content')

<div class="container-fluid ap-settings-hub">
    <div class="mb-4">
        <h4 class="mb-1">All Settings</h4>
        <p class="text-muted mb-0">Manage store configuration and integrations.</p>
    </div>

    @foreach ($settingsGroups as $group)
        <section class="ap-settings-group">
            <h5 class="ap-settings-group-title">
                <i class="{{ $group['icon'] }}" aria-hidden="true"></i>
                {{ $group['title'] }}
            </h5>
            <div class="row gutters-10">
                @foreach ($group['items'] as $item)
                    @php
                        $tileUrl = !empty($item['route']) ? route($item['route']) : ($item['url'] ?? null);
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        @if ($tileUrl)
                            <a href="{{ $tileUrl }}" class="ap-settings-tile" @if (!empty($item['external'])) target="_blank" rel="noopener" @endif>
                        @else
                            <div class="ap-settings-tile ap-settings-tile-disabled" aria-disabled="true" title="This settings page is not available yet">
                        @endif
                            <span class="ap-settings-tile-icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                            <span class="ap-settings-tile-text">
                                <span class="nm">{{ $item['name'] }}</span>
                                <span class="ds">{{ $item['description'] }}</span>
                            </span>
                        @if ($tileUrl)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>

<style>
    .ap-settings-group {
        margin-bottom: 1.75rem;
    }

    .ap-settings-group-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin: 0 0 .85rem;
        font-size: 1rem;
        font-weight: 650;
    }

    .ap-settings-group-title i {
        color: #267c70;
        font-size: 1.15rem;
    }

    .ap-settings-tile {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-height: 88px;
        height: 100%;
        padding: .9rem 1rem;
        color: inherit;
        background: #fff;
        border: 1px solid #e4e9e8;
        border-radius: 5px;
        text-decoration: none;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    a.ap-settings-tile:hover,
    a.ap-settings-tile:focus-visible {
        color: inherit;
        border-color: #267c70;
        box-shadow: 0 4px 14px rgba(29, 76, 69, .1);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .ap-settings-tile-icon {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        color: #267c70;
        background: #eaf4f1;
        border-radius: 5px;
        font-size: 1.35rem;
    }

    .ap-settings-tile-text {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: .2rem;
    }

    .ap-settings-tile-text .nm {
        font-weight: 600;
        line-height: 1.25;
    }

    .ap-settings-tile-text .ds {
        color: #697572;
        font-size: .82rem;
        line-height: 1.35;
    }

    .ap-settings-tile-disabled {
        cursor: not-allowed;
        background: #f7f8f8;
        color: #66706d;
    }

    .ap-settings-tile-disabled .ap-settings-tile-icon {
        color: #737d79;
        background: #e9edeb;
    }

    @media (prefers-reduced-motion: reduce) {
        .ap-settings-tile {
            transition: none;
        }
    }
</style>
@endsection