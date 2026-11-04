@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-home-edit-page', 'pageClass' => 'admin-home-edit-page'])
@section('page-title', 'Edit' . ' ' . ucfirst(str_replace('_', ' ', $section->section_name)))
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Home Page - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
<div class="container-fluid">
    <!-- <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit {{ ucfirst(str_replace('_', ' ', $section->section_name)) }}</h2>
                <a href="{{ route('admin.home-page.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div> -->

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Section Details</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($section->section_name === 'offers')
                        <div class="alert alert-info">
                            <strong>Special Offer section:</strong> The heading is controlled by <strong>Title</strong> (default: "Special Offer") and the intro text below it by <strong>Description</strong>. The offer banners are managed below under <strong>Offer Banners</strong>.
                        </div>
                    @endif

                    @if($section->section_name === 'deal_of_day')
                        <div class="alert alert-info">
                            <strong>!! Special Offer !! section:</strong> <strong>Title</strong>, <strong>Subtitle</strong> and <strong>Description</strong> now drive the heading, sub-heading and paragraph. <strong>Button Text</strong>, <strong>Button URL</strong> and <strong>Image</strong> work as before, and the countdown end date/time is set below.
                        </div>
                    @endif

                    @if($section->section_name === 'latest_post')
                        <div class="alert alert-info">
                            <strong>Latest Post section:</strong> <strong>Title</strong> = section heading, <strong>Description</strong> = intro text. The posts themselves come from published blog posts (manage them under the Blog menu); use the posts-count field below to control how many are shown.
                        </div>
                    @endif

                    @if($section->section_name === 'categories_heading')
                        <div class="alert alert-info">
                            <strong>All Categories section:</strong> <strong>Title</strong> = section heading (default: "All Categories"). Choose exactly which top-level categories appear on the home page using the multi-select below — leave empty to show all categories.
                        </div>
                    @endif

                    <form action="{{ route('admin.home-page.update', $section->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title" class="form-label"><strong>Title</strong></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $section->title) }}" placeholder="Enter section title">
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="subtitle" class="form-label"><strong>Subtitle</strong></label>
                            <input type="text" name="subtitle" id="subtitle" class="form-control @error('subtitle') is-invalid @enderror" 
                                   value="{{ old('subtitle', $section->subtitle) }}" placeholder="Enter section subtitle">
                            @error('subtitle')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label"><strong>Description</strong></label>
                            <textarea name="description" id="description" class="form-control textarea-control texteditor @error('description') is-invalid @enderror" 
                                      rows="4" placeholder="Enter section description">{{ old('description', $section->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="button_text" class="form-label"><strong>Button Text</strong></label>
                            <input type="text" name="button_text" id="button_text" class="form-control @error('button_text') is-invalid @enderror" 
                                   value="{{ old('button_text', $section->button_text) }}" placeholder="e.g., Shop Now, Learn More">
                            @error('button_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="button_url" class="form-label"><strong>Button URL</strong></label>
                            <input type="text" name="button_url" id="button_url" class="form-control @error('button_url') is-invalid @enderror" 
                                   value="{{ old('button_url', $section->button_url) }}" placeholder="e.g., /shop, /products">
                            @error('button_url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($section->section_name === 'services')
                        @php
                            $serviceItems = $section->extra_data['services'] ?? config('homepage.services', []);
                        @endphp
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Service Cards</h5>
                            </div>
                            <div class="card-body">
                                @foreach($serviceItems as $i => $service)
                                <div class="border rounded p-3 mb-3 bg-light">
                                    <strong class="d-block mb-2">Service {{ $i + 1 }}</strong>
                                    @if(!empty($service['image']))
                                        <div class="mb-2 service-image-current-{{ $i }}">
                                            <img src="{{ storedImageUrl($service['image'], 'assets/images/services') }}" alt="{{ $service['title'] ?? 'Service' }}" width="80" height="80" class="admin-image-thumb-bordered">
                                        </div>
                                    @endif
                                    <input type="hidden" name="services[{{ $i }}][existing_image]" value="{{ $service['image'] ?? '' }}">
                                    <input type="hidden" name="services[{{ $i }}][image_from_manager]" id="service_image_from_manager_{{ $i }}">
                                    <div id="impPreview_service_{{ $i }}" class="d-none mt-2"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 mb-3" onclick="impOpen_service_{{ $i }}()">
                                        <i class="fas fa-images me-1"></i> Change Image
                                    </button>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label" for="service_title_{{ $i }}">Title</label>
                                            <input type="text" id="service_title_{{ $i }}" name="services[{{ $i }}][title]" class="form-control" maxlength="100" value="{{ old('services.'.$i.'.title', $service['title'] ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="service_description_{{ $i }}">Description</label>
                                            <input type="text" id="service_description_{{ $i }}" name="services[{{ $i }}][description]" class="form-control" maxlength="255" value="{{ old('services.'.$i.'.description', $service['description'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($section->section_name === 'offers')
                        @php
                            $extra = $section->extra_data ?? [];
                            $banners = $extra['banners'] ?? [];
                            $maxBanners = 3;
                            $allBannerSlotsFilled = count($banners) >= $maxBanners;
                        @endphp
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Offer Banners</h5>
                                <button type="button" class="btn btn-sm btn-dark steve-btn {{ $allBannerSlotsFilled ? 'd-none' : '' }}" id="add-banner-btn">
                                    <i class="fas fa-plus me-1"></i> Add Banner
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Banners shown below the "Special Offer" heading on the home page (max {{ $maxBanners }}).</p>
                                <div id="banners-container">
                                    @for($i = 0; $i < $maxBanners; $i++)
                                    @php $banner = $banners[$i] ?? null; @endphp
                                    <div class="banner-row border rounded p-3 mb-3 bg-light {{ $banner ? '' : 'd-none' }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>Banner #{{ $i + 1 }}</strong>
                                            <button type="button" class="btn btn-sm btn-danger steve-btn remove-banner-btn"><i class="fas fa-times me-1"></i>Remove</button>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label mb-1"><strong>Image</strong></label>
                                                @if($banner && !empty($banner['image']))
                                                    @php
                                                        $previewImg = storedImageUrl($banner['image'], 'assets/images/home');
                                                    @endphp
                                                    <div class="mb-1 banner-image-current-{{ $i }}">
                                                        <img src="{{ $previewImg }}" width="100" class="admin-image-thumb-bordered" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                                                    </div>
                                                @endif
                                                <input type="hidden" name="banners[{{ $i }}][image_from_manager]" id="banners_image_from_manager_{{ $i }}">
                                                <input type="hidden" name="banners[{{ $i }}][existing_image]" value="{{ $banner['image'] ?? '' }}">
                                                <div id="impPreview_banner_{{ $i }}" class="d-none mt-1"></div>
                                                <div class="d-flex gap-2 mt-1 flex-wrap">
                                                    <button type="button" class="btn btn-outline-primary" onclick="impOpen_banner_{{ $i }}()">
                                                        <i class="fas fa-images me-1"></i> Browse Image Manager
                                                    </button>
                                                    <button type="button" id="clear_btn_banner_{{ $i }}" data-preview="impPreview_banner_{{ $i }}" class="btn btn-sm btn-outline-danger {{ !empty($banner['image']) ? '' : 'd-none' }}" onclick="clearPickerImage('banners_image_from_manager_{{ $i }}','impPreview_banner_{{ $i }}','.banner-image-current-{{ $i }}')">
                                                        <i class="fas fa-times me-1"></i> Clear
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1"><strong>Title</strong></label>
                                                        <input type="text" name="banners[{{ $i }}][title]" class="form-control" value="{{ $banner['title'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1"><strong>Subtitle</strong></label>
                                                        <input type="text" name="banners[{{ $i }}][subtitle]" class="form-control" value="{{ $banner['subtitle'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1"><strong>Button Text</strong></label>
                                                        <input type="text" name="banners[{{ $i }}][button_text]" class="form-control" value="{{ $banner['button_text'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1"><strong>Button URL</strong></label>
                                                        <input type="text" name="banners[{{ $i }}][button_url]" class="form-control" value="{{ $banner['button_url'] ?? '' }}" placeholder="/shop">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <p id="banners-full-note" class="text-muted small {{ $allBannerSlotsFilled ? '' : 'd-none' }}">Maximum {{ $maxBanners }} banners reached.</p>
                            </div>
                        </div>

                        <script>
                        document.getElementById('add-banner-btn')?.addEventListener('click', function() {
                            var hidden = document.querySelectorAll('#banners-container .banner-row.d-none');
                            if (hidden.length === 0) {
                                var note = document.getElementById('banners-full-note');
                                if (note) {
                                    note.classList.remove('d-none');
                                    setTimeout(function() { note.classList.add('d-none'); }, 2500);
                                }
                                return;
                            }
                            hidden[0].classList.remove('d-none');
                            if (document.querySelectorAll('#banners-container .banner-row').length === document.querySelectorAll('#banners-container .banner-row:not(.d-none)').length) {
                                document.getElementById('add-banner-btn').classList.add('d-none');
                                document.getElementById('banners-full-note').classList.remove('d-none');
                            }
                        });
                        document.querySelectorAll('.remove-banner-btn').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var row = btn.closest('.banner-row');
                                row.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
                                row.classList.add('d-none');
                                document.getElementById('add-banner-btn').classList.remove('d-none');
                                document.getElementById('banners-full-note').classList.add('d-none');
                            });
                        });
                        </script>
                        @endif

                        @if($section->section_name === 'services')
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Service Items</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addService()"><i class="fa fa-plus"></i> Add Service</button>
                            </div>
                            <div class="card-body">
                                <div id="services-container">
                                    @php
                                        $services = old('services', $section->extra_data['services'] ?? []);
                                        if (empty($services)) {
                                            $services = [['image'=>null,'title'=>'','description'=>''],['image'=>null,'title'=>'','description'=>''],['image'=>null,'title'=>'','description'=>''],['image'=>null,'title'=>'','description'=>'']];
                                        }
                                    @endphp
                                    @foreach($services as $i => $svc)
                                    <div class="border rounded p-3 mb-3 service-item">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Service #{{ $i+1 }}</strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.service-item').remove();"><i class="fa fa-trash"></i></button>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Image</label>
                                                <input type="hidden" name="services[{{ $i }}][existing_image]" value="{{ $svc['image'] ?? '' }}">
                                                <input type="hidden" name="services[{{ $i }}][image_from_manager]" id="services_image_from_manager_{{ $i }}">
                                                <div id="impPreview_services_{{ $i }}" class="d-none mb-2"></div>
                                                @if(!empty($svc['image']))
                                                <div class="mb-2 svc-current-{{ $i }}">
                                                    <img src="{{ storedImageUrl($svc['image'], 'assets/images/services') }}" style="max-width:100px;height:60px;object-fit:contain;border:1px solid #ddd;">
                                                </div>
                                                @endif
                                                <button type="button" class="btn btn-outline-primary" onclick="impOpen_services_{{ $i }}()">Browse Image Manager</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger {{ !empty($svc['image']) ? '' : 'd-none' }}" onclick="clearPickerImage('services_image_from_manager_{{ $i }}','impPreview_services_{{ $i }}','.svc-current-{{ $i }}',null); this.classList.add('d-none');">Clear</button>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="services[{{ $i }}][title]" class="form-control" value="{{ $svc['title'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Description</label>
                                                <input type="text" name="services[{{ $i }}][description]" class="form-control" value="{{ $svc['description'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($section->section_name === 'partners_heading')
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Partner Logos</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addPartner()"><i class="fa fa-plus"></i> Add Partner</button>
                            </div>
                            <div class="card-body">
                                <div id="partners-container">
                                    @php
                                        $partners = old('partners', $section->extra_data['partners'] ?? []);
                                    @endphp
                                    @foreach($partners as $i => $p)
                                    <div class="border rounded p-3 mb-3 partner-item">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Partner #{{ $i+1 }}</strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.partner-item').remove();"><i class="fa fa-trash"></i></button>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Logo</label>
                                                <input type="hidden" name="partners[{{ $i }}][existing_image]" value="{{ $p['image'] ?? '' }}">
                                                <input type="hidden" name="partners[{{ $i }}][image_from_manager]" id="partners_image_from_manager_{{ $i }}">
                                                <div id="impPreview_partners_{{ $i }}" class="d-none mb-2"></div>
                                                @if(!empty($p['image']))
                                                <div class="mb-2 partner-current-{{ $i }}">
                                                    <img src="{{ storedImageUrl($p['image'], 'assets/images/partner') }}" style="max-width:120px;height:60px;object-fit:contain;border:1px solid #ddd;background:#fff;">
                                                </div>
                                                @endif
                                                <button type="button" class="btn btn-outline-primary" onclick="impOpen_partners_{{ $i }}()">Browse Image Manager</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger {{ !empty($p['image']) ? '' : 'd-none' }}" onclick="clearPickerImage('partners_image_from_manager_{{ $i }}','impPreview_partners_{{ $i }}','.partner-current-{{ $i }}',null); this.classList.add('d-none');">Clear</button>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="partners[{{ $i }}][name]" class="form-control" value="{{ $p['name'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">URL (optional)</label>
                                                <input type="text" name="partners[{{ $i }}][url]" class="form-control" value="{{ $p['url'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if(empty($partners))
                                <p class="text-muted mb-0" id="partners-empty-note">No partners added yet. Click "Add Partner" to add logos.</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($section->section_name === 'buy_with_confidence')
                        @php
                            $extra = $section->extra_data ?? [];
                            $items = $extra['items'] ?? [
                                ['icon' => 'fa-solid fa-circle-check', 'title' => 'Fitment Guarantee', 'description' => 'Enter your VIN or vehicle specs and we show only parts that fit. Wrong part? We cover return shipping.'],
                                ['icon' => 'fa-solid fa-truck-fast', 'title' => 'Fast Shipping', 'description' => '5 distribution centers coast-to-coast. Most orders arrive in 1–3 business days. Free over $99.'],
                                ['icon' => 'fa-solid fa-rotate-left', 'title' => '30-Day Returns', 'description' => 'Parts don’t fit or change your mind? Return within 30 days. No restocking fee on most items.'],
                                ['icon' => 'fa-solid fa-user-gear', 'title' => 'Mechanic Tested', 'description' => 'Every brand we carry is vetted by ASE-certified technicians. No junk, no knock-offs, no surprises.'],
                            ];
                        @endphp
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Hotline Copy</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="hotline_heading" class="form-label">Lead-in</label>
                                    <input type="text" id="hotline_heading" name="hotline_heading" class="form-control" maxlength="255" value="{{ old('hotline_heading', $extra['hotline_heading'] ?? 'Not sure what fits?') }}">
                                </div>
                                <div class="form-group">
                                    <label for="hotline_description" class="form-label">Supporting text</label>
                                    <textarea id="hotline_description" name="hotline_description" class="form-control textarea-control" rows="3" maxlength="1000">{{ old('hotline_description', $extra['hotline_description'] ?? 'Call our parts hotline with your VIN and a real mechanic will match you. We read the diagrams so you don’t have to.') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Trust Cards</h5>
                            </div>
                            <div class="card-body">
                                @foreach($items as $i => $item)
                                <div class="border rounded p-3 mb-3 bg-light">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-2">
                                            <label class="form-label">Icon Class</label>
                                            <input type="text" name="items[{{ $i }}][icon]" class="form-control" value="{{ old('items.'.$i.'.icon', $item['icon'] ?? '') }}" placeholder="fa-solid fa-circle-check">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="items[{{ $i }}][title]" class="form-control" value="{{ old('items.'.$i.'.title', $item['title'] ?? '') }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Description</label>
                                            <textarea name="items[{{ $i }}][description]" class="form-control textarea-control" rows="2">{{ old('items.'.$i.'.description', $item['description'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($section->section_name === 'deal_of_day')
                        @php $extra = $section->extra_data ?? []; @endphp
                        <div class="form-group mb-3">
                            <label for="countdown" class="form-label"><strong>Countdown End Date/Time</strong></label>
                            <input type="datetime-local" name="countdown" id="countdown" class="form-control" value="{{ $extra['countdown'] ?? '' }}">
                            <small class="text-muted d-block">Sets the date/time shown in the countdown. Leave empty to use the default end date.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="deal_image" class="form-label"><strong>Content Image</strong></label>
                            <small class="text-muted d-block mb-2">This image appears as the section content overlay (above the countdown and button).</small>
                            <div class="mb-2">
                                @if($section->image)
                                    <div class="mb-2 deal-image-current">
                                        <img src="{{ storedImageUrl($section->image, 'assets/images/home') }}" alt="Current image"
                                             class="admin-image-preview">
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" name="image_from_manager" id="image_from_manager_deal_of_day_image">
                            <input type="hidden" name="remove_section_image" id="remove_section_image" value="0">
                            <div id="impPreview_deal_of_day_image" class="d-none mt-2"></div>
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('remove_section_image').value='0'; impOpen_deal_of_day_image()">
                                    <i class="fas fa-images me-1"></i> Browse Image Manager
                                </button>
                                <button type="button" id="clear_btn_deal_of_day_image" data-preview="impPreview_deal_of_day_image" data-current=".deal-image-current" class="btn btn-outline-danger {{ $section->image ? '' : 'd-none' }}" onclick="clearPickerImage('image_from_manager_deal_of_day_image','impPreview_deal_of_day_image','.deal-image-current','remove_section_image')">
                                    <i class="fas fa-times me-1"></i> Clear Image
                                </button>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="deal_bg_image" class="form-label"><strong>Background Image</strong></label>
                            <small class="text-muted d-block mb-2">This image sets the entire section background (cover + center). If not set, the content image will be used as background.</small>
                            @php $dealBgImage = $extra['deal_image'] ?? null; @endphp
    <div class="mb-2">
        @if($dealBgImage)
            <div class="mb-2 home-image-current">
                <img src="{{ storedImageUrl($dealBgImage, 'assets/images/home') }}" alt="Current background image"
                     class="admin-image-preview">
            </div>
        @endif
    </div>
                            <input type="hidden" name="deal_bg_image_from_manager" id="deal_bg_image_from_manager_deal_of_day">
                            <input type="hidden" name="deal_bg_image_existing" value="{{ $dealBgImage ?? '' }}">
                            <input type="hidden" name="remove_deal_bg_image" id="remove_deal_bg_image" value="0">
                            <div id="impPreview_deal_bg_image" class="d-none mt-2"></div>
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('remove_deal_bg_image').value='0'; impOpen_deal_bg_image()">
                                    <i class="fas fa-images me-1"></i> Browse Image Manager
                                </button>
                                <button type="button" id="clear_btn_deal_bg_image" data-preview="impPreview_deal_bg_image" data-current=".home-image-current" class="btn btn-sm btn-outline-danger {{ $dealBgImage ? '' : 'd-none' }}" onclick="clearPickerImage('deal_bg_image_from_manager_deal_of_day','impPreview_deal_bg_image','.home-image-current','remove_deal_bg_image')">
                                    <i class="fas fa-times me-1"></i> Clear Image
                                </button>
                            </div>
                        </div>
                        @endif
                        

                        @if($section->section_name === 'latest_post')
                        @php
                            $extra = $section->extra_data ?? [];
                            $allPosts = \App\Models\Blog::where('status', 'published')->orderByDesc('created_at')->get();
                            $selectedPostIds = collect($extra['post_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                        @endphp
                        <div class="form-group mb-3">
                            <label for="posts_count" class="form-label"><strong>Number of Posts to Show</strong></label>
                            <select name="posts_count" id="posts_count" class="form-control form-select">
                                @foreach([2, 3, 4, 6] as $n)
                                    <option value="{{ $n }}" {{ (int)($extra['posts_count'] ?? 2) === $n ? 'selected' : '' }}>{{ $n }} Posts</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">How many latest published blog posts to display on the home page.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="post_ids" class="form-label"><strong>Select Specific Posts for Home Page</strong></label>
                            <select name="post_ids[]" id="post_ids" class="form-control" multiple size="8">
                                @foreach($allPosts as $post)
                                    <option value="{{ $post->id }}" {{ in_array($post->id, $selectedPostIds) ? 'selected' : '' }}>
                                        {{ $post->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">Hold Ctrl/Cmd to select multiple posts and choose exactly which ones appear (shown in the order selected). Leave empty to use the count-based setting above.</small>
                        </div>
                        @endif

                        @if($section->section_name === 'categories_heading')
                        @php
                            $extra = $section->extra_data ?? [];
                            $selectedCategoryIds = collect($extra['category_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                            $allCategories = \App\Models\Category::whereNull('parent_id')->orderBy('name')->get();
                        @endphp
                        <div class="form-group mb-3">
                            <label for="category_ids" class="form-label"><strong>Select Categories for Home Page</strong></label>
                            <select name="category_ids[]" id="category_ids" class="form-control" multiple size="10">
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategoryIds) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">Hold Ctrl/Cmd to select multiple categories and choose exactly which ones appear (in the order selected). Leave empty to show all top-level categories.</small>
                        </div>
                        @endif

                        @if(in_array($section->section_name, ['top_brands_heading', 'top_brands', 'brands_section']))
                        @php
                            $extra = $section->extra_data ?? [];
                            $brandsLimit = $extra['brands_limit'] ?? '6';
                            $selectedBrandIds = collect($extra['brand_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                            $allBrands = \App\Models\Brand::where('status', true)->orderBy('name')->get();
                        @endphp
                        <div class="form-group mb-3">
                            <label for="brands_limit" class="form-label"><strong>Number of Brands to Show</strong></label>
                            <select name="brands_limit" id="brands_limit" class="form-control form-select">
                                <option value="6" {{ $brandsLimit == '6' ? 'selected' : '' }}>6 Brands</option>
                                <option value="9" {{ $brandsLimit == '9' ? 'selected' : '' }}>9 Brands</option>
                                <option value="12" {{ $brandsLimit == '12' ? 'selected' : '' }}>12 Brands</option>
                                <option value="18" {{ $brandsLimit == '18' ? 'selected' : '' }}>18 Brands</option>
                                <option value="all" {{ $brandsLimit == 'all' ? 'selected' : '' }}>All Brands</option>
                            </select>
                            <small class="text-muted d-block">How many brands to display on the home page if no specific brands are selected</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="brand_ids" class="form-label"><strong>Select Brands for Home Page</strong></label>
                            <select name="brand_ids[]" id="brand_ids" class="form-control" multiple size="8">
                                @foreach($allBrands as $brand)
                                    <option value="{{ $brand->id }}" {{ in_array($brand->id, $selectedBrandIds) ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">Hold Ctrl/Cmd to select multiple brands. Leave empty to use the count-based setting above.</small>
                        </div>
                        @endif

                        @if($section->section_name === 'explore_products')
                        @php
                            $extra = $section->extra_data ?? [];
                            $tabs = $extra['tabs'] ?? [];
                        @endphp
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tab-wise Product Selection</h5>
                                <button type="button" class="btn btn-sm btn-light steve-btn" onclick="addTab()">+ Add Tab</button>
                            </div>
                            <div class="card-body" id="tabs-container">
                                <p class="text-muted small">Manage the tabs shown in the Explore Our Products section. Each tab shows up to 8 products.</p>
                                @foreach($tabs as $i => $tab)
                                @php
                                    $selectedProductIds = array_values(array_filter(array_map('intval', $tab['product_ids'] ?? [])));
                                    $selectedProducts = \App\Models\Product::whereIn('id', $selectedProductIds)->get(['id', 'name', 'part_number'])->keyBy('id');
                                @endphp
                                <div class="tab-row border rounded p-3 mb-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="flex-grow-1 me-3">
                                            <label class="form-label mb-0 fw-600">Tab Heading</label>
                                            <input type="text" name="tabs[{{ $i }}][label]" class="form-control" value="{{ $tab['label'] ?? '' }}" placeholder="e.g., New Arrivals">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-4 steve-btn btn-w40" onclick="this.closest('.tab-row').remove()" style="font-size: 18px;">x</button>
                                    </div>
                                    <label class="form-label mb-0 fw-600">Products</label>
                                    <div class="tab-product-picker" data-tab-index="{{ $i }}">
                                        <input type="search" class="form-control tab-product-search" placeholder="Search by product name or part number" autocomplete="off">
                                        <div class="list-group tab-product-results d-none mt-1"></div>
                                        <div class="tab-product-selected d-flex flex-wrap gap-2 mt-2">
                                            @foreach($selectedProductIds as $productId)
                                                @if($selectedProducts->has($productId))
                                                    @php $selectedProduct = $selectedProducts->get($productId); @endphp
                                                    <span class="badge bg-secondary d-inline-flex align-items-center gap-2 p-2" data-product-id="{{ $productId }}">
                                                        {{ $selectedProduct->name }} ({{ $selectedProduct->part_number }})
                                                        <button type="button" class="btn-close btn-close-white tab-product-remove" aria-label="Remove product"></button>
                                                        <input type="hidden" name="tabs[{{ $i }}][product_ids][]" value="{{ $productId }}">
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Choose up to 8 products. Search by name or part number.</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <template id="tab-template">
                            <div class="tab-row border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="flex-grow-1 me-3">
                                        <label class="form-label mb-0 fw-600">Tab Heading</label>
                                        <input type="text" name="tabs[__INDEX__][label]" class="form-control" placeholder="e.g., New Arrivals">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-4 steve-btn" onclick="this.closest('.tab-row').remove()">x</button>
                                </div>
                                <label class="form-label mb-0 fw-600">Products</label>
                                <div class="tab-product-picker" data-tab-index="__INDEX__">
                                    <input type="search" class="form-control tab-product-search" placeholder="Search by product name or part number" autocomplete="off">
                                    <div class="list-group tab-product-results d-none mt-1"></div>
                                    <div class="tab-product-selected d-flex flex-wrap gap-2 mt-2"></div>
                                    <small class="text-muted">Choose up to 8 products. Search by name or part number.</small>
                                </div>
                            </div>
                        </template>

                        <script>
                        let tabIndex = {{ count($tabs) }};
                        const productSearchUrl = @json(route('admin.home-page.product-search'));

                        function initTabProductPicker(picker) {
                            const input = picker.querySelector('.tab-product-search');
                            const results = picker.querySelector('.tab-product-results');
                            const selected = picker.querySelector('.tab-product-selected');
                            let timer;

                            function addProduct(product) {
                                if (selected.querySelector('[data-product-id="' + product.id + '"]')) return;
                                if (selected.querySelectorAll('[data-product-id]').length >= 8) {
                                    input.setCustomValidity('Choose no more than 8 products.');
                                    input.reportValidity();
                                    return;
                                }

                                input.setCustomValidity('');
                                const chip = document.createElement('span');
                                chip.className = 'badge bg-secondary d-inline-flex align-items-center gap-2 p-2';
                                chip.dataset.productId = product.id;

                                const label = document.createElement('span');
                                label.textContent = product.label;
                                const remove = document.createElement('button');
                                remove.type = 'button';
                                remove.className = 'btn-close btn-close-white tab-product-remove';
                                remove.setAttribute('aria-label', 'Remove product');
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'tabs[' + picker.dataset.tabIndex + '][product_ids][]';
                                hidden.value = product.id;

                                chip.append(label, remove, hidden);
                                selected.appendChild(chip);
                            }

                            input.addEventListener('input', function() {
                                clearTimeout(timer);
                                const search = input.value.trim();
                                if (search.length < 2) {
                                    results.replaceChildren();
                                    results.classList.add('d-none');
                                    return;
                                }

                                timer = setTimeout(function() {
                                    fetch(productSearchUrl + '?search=' + encodeURIComponent(search), {
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(response => response.ok ? response.json() : [])
                                    .then(products => {
                                        results.replaceChildren();
                                        products.forEach(product => {
                                            if (selected.querySelector('[data-product-id="' + product.id + '"]')) return;
                                            const option = document.createElement('button');
                                            option.type = 'button';
                                            option.className = 'list-group-item list-group-item-action';
                                            option.textContent = product.label;
                                            option.addEventListener('click', function() {
                                                addProduct(product);
                                                input.value = '';
                                                results.replaceChildren();
                                                results.classList.add('d-none');
                                            });
                                            results.appendChild(option);
                                        });
                                        results.classList.toggle('d-none', results.childElementCount === 0);
                                    })
                                    .catch(() => {
                                        results.replaceChildren();
                                        results.classList.add('d-none');
                                    });
                                }, 250);
                            });

                            selected.addEventListener('click', function(event) {
                                const remove = event.target.closest('.tab-product-remove');
                                if (remove) remove.closest('[data-product-id]').remove();
                            });
                        }

                        document.querySelectorAll('.tab-product-picker').forEach(initTabProductPicker);

                        function addTab() {
                            const html = document.getElementById('tab-template').innerHTML.replace(/__INDEX__/g, tabIndex++);
                            const div = document.createElement('div');
                            div.innerHTML = html;
                            const row = div.firstElementChild;
                            document.getElementById('tabs-container').appendChild(row);
                            initTabProductPicker(row.querySelector('.tab-product-picker'));
                        }
                        </script>
                        @endif

                        @if($section->section_name !== 'deal_of_day')
                        <div class="form-group mb-3">
                            <label for="image" class="form-label"><strong>Image / Background Image</strong></label>
                            <small class="text-muted d-block mb-2">If you set it, this image will become the background for this section on the home page (Cover + Center).</small>
                        <div class="mb-2">
                            @if($section->image)
                                <div class="mb-2 home-image-current">
                                    <img src="{{ storedImageUrl($section->image, 'assets/images/home') }}" alt="Current image"
                                        class="admin-image-preview">
                                </div>
                            @endif
                        </div>
                            <input type="hidden" name="image_from_manager" id="image_from_manager_home_image">
                            <input type="hidden" name="remove_section_image" id="remove_section_image" value="0">
                            <div id="impPreview_home_image" class="d-none mt-2"></div>
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('remove_section_image').value='0'; impOpen_home_image()">
                                    <i class="fas fa-images me-1"></i> Browse Image Manager
                                </button>
                                <button type="button" id="clear_btn_home_image" data-preview="impPreview_home_image" data-current=".home-image-current" class="btn btn-outline-danger {{ $section->image ? '' : 'd-none' }}" onclick="clearPickerImage('image_from_manager_home_image','impPreview_home_image','.home-image-current','remove_section_image')">
                                    <i class="fas fa-times me-1"></i> Clear Image
                                </button>
                            </div>
                            @error('image')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif

                        <div class="form-group mb-3">
                            <label for="status" class="form-label">
                                <input type="checkbox" name="status" id="status" value="1" 
                                       {{ old('status', $section->status) ? 'checked' : '' }}>
                                <strong>Active</strong>
                            </label>
                            <small class="text-muted d-block">Check this to display this section on the home page</small>
                        </div>

                        <div class="form-group d-flex action-buttons">
                            <button type="submit" class="btn btn-primary steve-btn gap-1" style="display: flex; align-items: center;">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.home-page.index') }}" class="btn btn-primary steve-btn gap-1" style="display: flex; align-items: center;">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Section Information</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Section Name:</strong>
                        {{ ucfirst(str_replace('_', ' ', $section->section_name)) }}
                    </p>
                    <p>
                        <strong>Order:</strong>
                        {{ $section->order }}
                    </p>
                    <p>
                        <strong>Status:</strong>
                        <span class="badge {{ $section->status ? 'bg-light text-success border border-success-subtle' : 'bg-light text-danger border border-danger-subtle' }}">
                            {{  $section->status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                    <p>
                        <strong>Joined:</strong>
                        {{ $section->created_at->format('M d, Y H:i') }}
                    </p>
                    <p>
                        <strong>Last Updated:</strong>
                        {{ $section->updated_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .btn {
        margin-right: 0.5rem;
    }
</style>

<script>
// Clear a selected/picked image (hidden input + preview + current image) for image-manager pickers.
function clearPickerImage(hiddenId, previewId, currentImgSel, removeFlagId) {
    var hidden = document.getElementById(hiddenId);
    if (hidden) hidden.value = '';
    var preview = document.getElementById(previewId);
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('d-none');
    }
    if (currentImgSel) {
        document.querySelectorAll(currentImgSel).forEach(function(el) {
            el.classList.add('d-none');
        });
    }
    if (removeFlagId) {
        var flag = document.getElementById(removeFlagId);
        if (flag) flag.value = '1';
    }
}
</script>

<script>
// Show the Clear button when an image is present — either an already-saved image
// (.home-image-current / .deal-image-current etc.) OR a newly picked one (preview div).
document.addEventListener('DOMContentLoaded', function() {
    var obsConfig = { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] };
    document.querySelectorAll('[data-preview]').forEach(function(btn) {
        var previewId  = btn.getAttribute('data-preview');
        var currentSel = btn.getAttribute('data-current');   // e.g. ".home-image-current"
        var preview    = document.getElementById(previewId);
        if (!preview) return;

        var update = function() {
            // 1. New image just picked from the manager?
            var hasPickedImage = preview.querySelector('img') !== null;

            // 2. Existing saved image still visible? (not yet cleared by user click)
            var hasExistingImage = false;
            if (currentSel) {
                var existing = document.querySelectorAll(currentSel);
                existing.forEach(function(el) {
                    if (!el.classList.contains('d-none')) hasExistingImage = true;
                });
            }

            btn.classList.toggle('d-none', !hasPickedImage && !hasExistingImage);
        };

        // Watch the preview div for new picks
        new MutationObserver(update).observe(preview, obsConfig);

        // Also watch each existing-image container so the button hides after Clear is clicked
        if (currentSel) {
            document.querySelectorAll(currentSel).forEach(function(el) {
                new MutationObserver(update).observe(el, { attributes: true, attributeFilter: ['class'] });
            });
        }

        update(); // run once on page load
    });
});
</script>

<script>
// Keep "Select Specific Posts" selection capped at "Number of Posts to Show".
function enforcePostLimit() {
    var countSel = document.getElementById('posts_count');
    var postSel = document.getElementById('post_ids');
    if (!countSel || !postSel) return;
    var max = parseInt(countSel.value, 10) || 2;
    var opts = Array.prototype.slice.call(postSel.selectedOptions);
    if (opts.length > max) {
        for (var i = opts.length - 1; i >= max; i--) {
            opts[i].selected = false;
        }
    }
}
document.addEventListener('DOMContentLoaded', function () {
    var countSel = document.getElementById('posts_count');
    var postSel = document.getElementById('post_ids');
    if (countSel) countSel.addEventListener('change', enforcePostLimit);
    if (postSel) postSel.addEventListener('change', enforcePostLimit);
});
</script>

<script>
(function () {
    var MAX_PARTNERS = 20; // pickers are pre-rendered for partners[0..19]
    var MAX_SERVICES = 4;  // controller saves max 4 services

    var partnerIndex = {{ isset($partners) && count($partners) ? (max(array_keys($partners)) + 1) : 0 }};
    var serviceIndex = {{ isset($services) && count($services) ? (max(array_keys($services)) + 1) : 0 }};

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function partnerRowHtml(i) {
        return '<div class="border rounded p-3 mb-3 partner-item">' +
            '<div class="d-flex justify-content-between mb-2">' +
                '<strong>Partner #' + (i + 1) + '</strong>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.partner-item\').remove();"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">Logo</label>' +
                    '<input type="hidden" name="partners[' + i + '][existing_image]" value="">' +
                    '<input type="hidden" name="partners[' + i + '][image_from_manager]" id="partners_image_from_manager_' + i + '">' +
                    '<div id="impPreview_partners_' + i + '" class="d-none mb-2"></div>' +
                    '<button type="button" class="btn btn-outline-primary" onclick="impOpen_partners_' + i + '()">Browse Image Manager</button>' +
                    '<button type="button" class="btn btn-outline-danger d-none" onclick="clearPickerImage(\'partners_image_from_manager_' + i + '\',\'impPreview_partners_' + i + '\',null,null); this.classList.add(\'d-none\');">Clear</button>' +
                '</div>' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">Name</label>' +
                    '<input type="text" name="partners[' + i + '][name]" class="form-control">' +
                '</div>' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">URL (optional)</label>' +
                    '<input type="text" name="partners[' + i + '][url]" class="form-control">' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function serviceRowHtml(i) {
        return '<div class="border rounded p-3 mb-3 service-item">' +
            '<div class="d-flex justify-content-between mb-2">' +
                '<strong>Service #' + (i + 1) + '</strong>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.service-item\').remove();"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">Image</label>' +
                    '<input type="hidden" name="services[' + i + '][existing_image]" value="">' +
                    '<input type="hidden" name="services[' + i + '][image_from_manager]" id="services_image_from_manager_' + i + '">' +
                    '<div id="impPreview_services_' + i + '" class="d-none mb-2"></div>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary" onclick="impOpen_services_' + i + '()">Browse Image Manager</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger d-none" onclick="clearPickerImage(\'services_image_from_manager_' + i + '\',\'impPreview_services_' + i + '\',null,null); this.classList.add(\'d-none\');">Clear</button>' +
                '</div>' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">Title</label>' +
                    '<input type="text" name="services[' + i + '][title]" class="form-control">' +
                '</div>' +
                '<div class="col-md-4 mb-2">' +
                    '<label class="form-label">Description</label>' +
                    '<input type="text" name="services[' + i + '][description]" class="form-control">' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function appendRow(container, html) {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        container.appendChild(wrapper.firstElementChild);
    }

    window.addPartner = function () {
        var container = document.getElementById('partners-container');
        if (!container) return;
        if (partnerIndex >= MAX_PARTNERS) {
            alert('Maximum ' + MAX_PARTNERS + ' partners allowed.');
            return;
        }
        appendRow(container, partnerRowHtml(partnerIndex));
        partnerIndex++;
        var note = document.getElementById('partners-empty-note');
        if (note) note.classList.add('d-none');
    };

    window.addService = function () {
        var container = document.getElementById('services-container');
        if (!container) return;
        if (serviceIndex >= MAX_SERVICES) {
            alert('Maximum ' + MAX_SERVICES + ' services allowed.');
            return;
        }
        appendRow(container, serviceRowHtml(serviceIndex));
        serviceIndex++;
    };
})();
</script>

<script>
if (typeof window.addPartner !== 'function') {
    window.addPartner = function () {
        var container = document.getElementById('partners-container');
        if (!container) { alert('Partners container not found'); return; }
        var items = container.querySelectorAll('.partner-item');
        if (items.length >= 20) { alert('Maximum 20 partners allowed.'); return; }
        var i = items.length;
        var html = '<div class="border rounded p-3 mb-3 partner-item"><div class="d-flex justify-content-between mb-2"><strong>Partner #' + (i+1) + '</strong><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.partner-item\').remove();"><i class="fa fa-trash"></i></button></div><div class="row"><div class="col-md-4 mb-2"><label class="form-label">Logo</label><input type="hidden" name="partners[' + i + '][existing_image]" value=""><input type="hidden" name="partners[' + i + '][image_from_manager]" id="partners_image_from_manager_' + i + '"><div id="impPreview_partners_' + i + '" class="d-none mb-2"></div><button type="button" class="btn btn-outline-primary" onclick="impOpen_partners_' + i + '()">Browse Image Manager</button><button type="button" class="btn btn-sm btn-outline-danger d-none" onclick="clearPickerImage(\'partners_image_from_manager_' + i + '\',\'impPreview_partners_' + i + '\',null,null); this.classList.add(\'d-none\');">Clear</button></div><div class="col-md-4 mb-2"><label class="form-label">Name</label><input type="text" name="partners[' + i + '][name]" class="form-control"></div><div class="col-md-4 mb-2"><label class="form-label">URL (optional)</label><input type="text" name="partners[' + i + '][url]" class="form-control"></div></div></div>';
        var d = document.createElement('div');
        d.innerHTML = html;
        container.appendChild(d.firstElementChild);
        var note = document.getElementById('partners-empty-note');
        if (note) note.classList.add('d-none');
    };
}
</script>

@include('admin.partials.image-manager-picker', ['pickerId' => 'home_image', 'targetInput' => 'image_from_manager'])

@for($i = 0; $i < 3; $i++)
@include('admin.partials.image-manager-picker', ['pickerId' => 'banner_' . $i, 'targetInput' => 'banners[' . $i . '][image_from_manager]'])
@endfor

@if($section->section_name === 'services')
@for($i = 0; $i < 4; $i++)
@include('admin.partials.image-manager-picker', ['pickerId' => 'services_' . $i, 'targetInput' => 'services[' . $i . '][image_from_manager]'])
@endfor
@endif

@if($section->section_name === 'partners_heading')
@for($i = 0; $i < 20; $i++)
@include('admin.partials.image-manager-picker', ['pickerId' => 'partners_' . $i, 'targetInput' => 'partners[' . $i . '][image_from_manager]'])
@endfor
@endif

@if($section->section_name === 'deal_of_day')
@include('admin.partials.image-manager-picker', ['pickerId' => 'deal_of_day_image', 'targetInput' => 'image_from_manager'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'deal_bg_image', 'targetInput' => 'deal_bg_image_from_manager'])
@endif

@endsection
