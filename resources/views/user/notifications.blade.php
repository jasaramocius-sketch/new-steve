@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-notifications-page', 'pageClass' => 'user-notifications-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Notifications - StAutoparts',
        'metaTitle' => 'Notifications | StAutoparts',
        'metaDescription' => 'View your notifications at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">Notifications
        @if($unreadCount > 0)
        <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
        @endif
    </h4>
    @if($unreadCount > 0)
    <form action="{{ route('user.notifications.read-all') }}" method="POST">
        @csrf
        <button class="btn btn-outline-secondary steve-btn">Mark All as Read</button>
    </form>
    @endif
</div>

<div class="list-group">
@forelse($notifications as $notification)
<div class="list-group-item d-flex justify-content-between align-items-start {{ $notification->is_read ? '' : 'fw-bold bg-light' }}">
    <div class="me-3">
        <div>{{ $notification->title }}</div>
        <small class="text-muted">{!!
            str_contains($notification->message, '<a')
                ? $notification->message
                : preg_replace('/#(ORD[A-Z0-9]+)/i', '<a href="' . url('/user/orders/$1') . '" class="text-decoration-underline">$0</a>', e($notification->message))
        !!}</small>
        <br><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
    </div>
@unless($notification->is_read)
    <div class="action-buttons">
        <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="action-btn btn-view-live" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Mark Read">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>
        </form>
    </div>
@endunless
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <p class="text-muted mb-0">No notifications yet.</p>
    </div>
</div>
@endforelse
</div>

@if(method_exists($notifications, 'links'))
    <div class="pagination-wrapper mt-4">
        {{ $notifications->links() }}
    </div>
@endif
@endsection