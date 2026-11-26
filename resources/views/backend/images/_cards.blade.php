@foreach($images as $image)
@php $hasWebp = $image->hasWebpVersion(); @endphp
<div class="grid-item">
    <div class="card border h-100 image-card" data-id="{{ $image->id }}">
        <div class="select-overlay">
            <div class="check-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
        <div class="list-bulk-check">
            <div class="bulk-check"></div>
        </div>
        @if($hasWebp && $image->mime_type !== 'image/webp')
            <span class="position-absolute top-0 start-0 m-2 badge bg-success z-3" title="Already converted to WebP"><i class="fas fa-check"></i> WebP</span>
        @endif
        <a href="{{ route('admin.images.edit', $image->id) }}" class="text-decoration-none text-dark image-edit-link d-flex gap-1">
            <div class="thumb-wrap admin-image-thumb-wrap">
                <img src="{{ $image->thumb_url }}" alt="{{ $image->alt_text ?? $image->original_name }}" class="admin-image-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ asset("assets/images/placeholder.png") }}'">
            </div>
            <div class="card-info p-2 small">
                <div class="info-name">
                    <div class="text-truncate fw-medium" title="{{ $image->original_name }}">{{ $image->original_name }}</div>
                </div>
                <div class="info-meta grid-view-only">{{ $image->size_in_kb }} | {{ $image->width && $image->height ? $image->width.'x'.$image->height : '—' }}</div>
                <div class="info-meta grid-view-only"></div>
                <div class="card-badges d-flex justify-content-between mt-1 gap-1 flex-wrap grid-view-only">
                    <span class="badge {{ $image->attachable_type ? 'bg-light text-success border border-success-subtle' : 'bg-light text-secondary border border-secondary-subtle' }}">
                        {{ $image->attachable_type ? class_basename($image->attachable_type) : 'Unused' }}
                    </span>
                    @if(in_array($image->mime_type, ['image/jpeg', 'image/pjpeg', 'image/jpg']))
                        <span class="badge bg-light text-warning border border-warning-subtle">JPEG</span>
                    @elseif($image->mime_type === 'image/png')
                        <span class="badge bg-light text-primary border border-primary-subtle">PNG</span>
                    @elseif($image->mime_type === 'image/gif')
                        <span class="badge bg-light text-purple border border-purple-subtle admin-gif-badge">GIF</span>
                    @elseif($image->mime_type === 'image/webp')
                        <span class="badge bg-light text-info border border-info-subtle">WebP</span>
                    @elseif($image->mime_type === 'image/svg+xml')
                        <span class="badge bg-light text-secondary border border-secondary-subtle">SVG</span>
                    @endif
                </div>
                <div class="info-meta list-view-only">{{ $image->size_in_kb }} | {{ $image->width && $image->height ? $image->width.'x'.$image->height : '—' }}</div>
                <div class="card-badges d-flex justify-content-between mt-1 gap-1 flex-wrap list-view-only">
                    <div class="info-type list-view-only">
                        @if(in_array($image->mime_type, ['image/jpeg', 'image/pjpeg', 'image/jpg']))
                            <span class="badge bg-light text-warning border border-warning-subtle">JPEG</span>
                        @elseif($image->mime_type === 'image/png')
                            <span class="badge bg-light text-primary border border-primary-subtle">PNG</span>
                        @elseif($image->mime_type === 'image/gif')
                            <span class="badge bg-light border admin-gif-badge">GIF</span>
                        @elseif($image->mime_type === 'image/webp')
                            <span class="badge bg-light text-info border border-info-subtle">WebP</span>
                        @elseif($image->mime_type === 'image/svg+xml')
                            <span class="badge bg-light text-secondary border border-secondary-subtle">SVG</span>
                        @endif
                    </div>
                    <div class="info-usage list-view-only">
                        <span class="badge {{ $image->attachable_type ? 'bg-light text-success border border-success-subtle' : 'bg-light text-secondary border border-secondary-subtle' }}">
                            {{ $image->attachable_type ? class_basename($image->attachable_type) : 'Unused' }}
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endforeach