@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-seo-page', 'pageClass' => 'admin-settings-seo-page'])
@section('page-title', 'SEO Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'SEO Settings - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <!-- <h4 class="mb-1">SEO Settings</h4> -->
            <p class="text-muted mb-0">Search appearance, verification, analytics and crawler rules.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="fas fa-sitemap me-1"></i> View Sitemap
            </a>
            <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="fas fa-robot me-1"></i> View robots.txt
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (($settings['seo_indexing_enabled'] ?? '1') === '0')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Search engine indexing is currently disabled. Public pages will send <code>noindex, nofollow</code>.
        </div>
    @elseif (preg_match('/^\s*Disallow:\s*\/\s*$/mi', $settings['seo_robots_txt'] ?? ''))
        <div class="alert alert-danger">
            <i class="fas fa-ban me-1"></i>
            robots.txt contains <code>Disallow: /</code> while indexing is enabled. The site will remain blocked until that line is removed.
        </div>
    @endif

    <form action="{{ route('admin.settings.seo.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Search Appearance</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Site Title</strong></label>
                            <input type="text" name="seo_site_title" class="form-control"
                                value="{{ old('seo_site_title', $settings['seo_site_title'] ?? '') }}"
                                placeholder="{{ config('app.name') }}">
                            <small class="text-muted">Used as the brand suffix in page titles and Open Graph tags.</small>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Title Separator</strong></label>
                            <input type="text" name="seo_title_separator" class="form-control"
                                value="{{ old('seo_title_separator', $settings['seo_title_separator'] ?? '|') }}"
                                placeholder="|">
                            <small class="text-muted">Shown between the page title and site title.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label"><strong>Default Meta Title</strong></label>
                    <input type="text" name="seo_default_meta_title" class="form-control" maxlength="255"
                        value="{{ old('seo_default_meta_title', $settings['seo_default_meta_title'] ?? '') }}"
                        placeholder="Auto parts for cars, trucks and SUVs">
                    <small class="text-muted">Fallback when a page does not define its own meta title.</small>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label"><strong>Default Meta Description</strong></label>
                    <textarea name="seo_default_meta_description" rows="3" class="form-control textarea-control" maxlength="500"
                        placeholder="Shop quality auto parts with fast shipping and expert support.">{{ old('seo_default_meta_description', $settings['seo_default_meta_description'] ?? '') }}</textarea>
                    <small class="text-muted">Aim for 150–160 characters for best search results.</small>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label"><strong>Default Meta Keywords</strong></label>
                    <input type="text" name="seo_default_meta_keywords" class="form-control" maxlength="500"
                        value="{{ old('seo_default_meta_keywords', $settings['seo_default_meta_keywords'] ?? '') }}"
                        placeholder="auto parts, car parts, truck parts">
                    <small class="text-muted">Optional. Most search engines ignore this tag, but it is available for other tools.</small>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Twitter / X Handle</strong></label>
                            <input type="text" name="seo_twitter_handle" class="form-control" maxlength="100"
                                value="{{ old('seo_twitter_handle', $settings['seo_twitter_handle'] ?? '') }}"
                                placeholder="@yourhandle">
                            <small class="text-muted">Used for Twitter card attribution.</small>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Default Social Share Image</strong></label>
                            @php $ogImage = $settings['seo_default_og_image'] ?? ''; @endphp
                            @if (!empty($ogImage))
                                <div class="mb-2" id="seoOgImageCurrent">
                                    <img src="{{ storedImageUrl($ogImage, 'assets/images') }}" alt="Default social share image"
                                        class="img-fluid rounded border" style="max-height: 120px;">
                                </div>
                            @endif
                            <input type="hidden" name="image_from_manager_seo_default_og_image" id="image_from_manager_seo_default_og_image">
                            <input type="hidden" name="remove_seo_default_og_image" id="remove_seo_default_og_image" value="0">
                            <div id="impPreview_seo_default_og_image" class="d-none mt-2"></div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="document.getElementById('remove_seo_default_og_image').value='0'; impOpen_seo_default_og_image()">
                                    <i class="fas fa-images me-1"></i> Browse Image Manager
                                </button>
                                @if (!empty($ogImage))
                                    <button type="button" class="btn btn-outline-danger" onclick="clearSeoOgImage()">
                                        <i class="fas fa-times me-1"></i> Clear Image
                                    </button>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-1">Recommended size: 1200 × 630 px.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Search Engine Verification</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Google Search Console</strong></label>
                            <input type="text" name="seo_google_site_verification" class="form-control"
                                value="{{ old('seo_google_site_verification', $settings['seo_google_site_verification'] ?? '') }}"
                                placeholder="Verification code">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Bing Webmaster Tools</strong></label>
                            <input type="text" name="seo_bing_site_verification" class="form-control"
                                value="{{ old('seo_bing_site_verification', $settings['seo_bing_site_verification'] ?? '') }}"
                                placeholder="msvalidate.01 code">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Pinterest</strong></label>
                            <input type="text" name="seo_pinterest_verification" class="form-control"
                                value="{{ old('seo_pinterest_verification', $settings['seo_pinterest_verification'] ?? '') }}"
                                placeholder="p:domain_verify code">
                        </div>
                    </div>
                </div>
                <small class="text-muted">Only the verification value is needed — do not paste the full meta tag.</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Analytics &amp; Tracking</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Google Analytics 4</strong></label>
                            <input type="text" name="seo_ga4_measurement_id" class="form-control"
                                value="{{ old('seo_ga4_measurement_id', $settings['seo_ga4_measurement_id'] ?? '') }}"
                                placeholder="G-XXXXXXXXXX">
                            <small class="text-muted">Measurement ID, not the old UA property.</small>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Google Tag Manager</strong></label>
                            <input type="text" name="seo_gtm_container_id" class="form-control"
                                value="{{ old('seo_gtm_container_id', $settings['seo_gtm_container_id'] ?? '') }}"
                                placeholder="GTM-XXXXXXX">
                            <small class="text-muted">Container ID. Loads the GTM snippet on the storefront.</small>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Facebook Pixel</strong></label>
                            <input type="text" name="seo_facebook_pixel_id" class="form-control"
                                value="{{ old('seo_facebook_pixel_id', $settings['seo_facebook_pixel_id'] ?? '') }}"
                                placeholder="123456789012345">
                            <small class="text-muted">Tracks PageView events on the storefront.</small>
                        </div>
                    </div>
                </div>
                <small class="text-muted">Leave a field blank to disable that integration.</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Robots &amp; Sitemap</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="seo_indexing_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="seo_indexing_enabled"
                                name="seo_indexing_enabled" value="1"
                                {{ old('seo_indexing_enabled', $settings['seo_indexing_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="seo_indexing_enabled">
                                <strong>Allow search engines to index the site</strong>
                            </label>
                            <div class="text-muted small">When disabled, public pages send noindex, nofollow and robots.txt blocks crawlers.</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="seo_sitemap_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="seo_sitemap_enabled"
                                name="seo_sitemap_enabled" value="1"
                                {{ old('seo_sitemap_enabled', $settings['seo_sitemap_enabled'] ?? '1') !== '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="seo_sitemap_enabled">
                                <strong>Enable XML sitemap</strong>
                            </label>
                            @if (($settings['seo_sitemap_enabled'] ?? '1') !== '0')
                                <div class="text-muted small">Serves <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="">{{ route('sitemap') }}</a> with products, categories, brands and blog posts.</div>
                            @else
                                <div class="mt-2 small text-muted">Sitemap is currently disabled.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="form-label"><strong>robots.txt Content</strong></label>
                    <textarea name="seo_robots_txt" id="seo_robots_txt" rows="10" class="form-control textarea-control form-control-sm font-monospace"
                        placeholder="Leave blank to auto-generate robots.txt for the current domain.">{{ old('seo_robots_txt', $settings['seo_robots_txt'] ?? '') }}</textarea>
                    <div class="d-flex flex-wrap justify-content-between gap-1 mt-1">
                        <small class="text-muted">Leave blank to auto-generate. The sitemap URL follows the current domain automatically, so it keeps working after moving to a live domain.</small>
                        <span class="d-inline-flex gap-3">
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="document.getElementById('seo_robots_txt').value = defaultRobotsTxtValue">
                                Use default content
                            </button>
                            <button type="button" class="btn btn-sm btn-link p-0 text-danger" onclick="document.getElementById('seo_robots_txt').value = ''">
                                Clear (auto-generate)
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-primary steve-btn">
                <i class="fas fa-save me-1"></i> Save SEO Settings
            </button>
        </div>
    </form>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'seo_default_og_image', 'targetInput' => 'image_from_manager_seo_default_og_image'])

@push('scripts')
<script>
var defaultRobotsTxtValue = @json($defaultRobots);

function clearSeoOgImage() {
    var input = document.getElementById('image_from_manager_seo_default_og_image');
    if (input) input.value = '';

    var preview = document.getElementById('impPreview_seo_default_og_image');
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('d-none');
    }

    var current = document.getElementById('seoOgImageCurrent');
    if (current) current.classList.add('d-none');

    var flag = document.getElementById('remove_seo_default_og_image');
    if (flag) flag.value = '1';
}
</script>
@endpush
@endsection
