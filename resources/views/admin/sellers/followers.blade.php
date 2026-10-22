@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-sellers-followers-page', 'pageClass' => 'admin-sellers-followers-page'])
@section('page-title', 'Followers - ' . $seller->name)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Seller Followers - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0">Followers of "{{ $seller->name }}" ({{ $followers->total() }})</h5>
    </div>
    <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Seller</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Followed On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followers as $follow)
                    <tr>
                        <td class="ps-3">{{ $follow->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm bg-light d-inline-flex align-items-center justify-content-center rounded-circle admin-avatar-32">
                                    {{ strtoupper(substr($follow->user->name ?? '?', 0, 1)) }}
                                </span>
                                {{ $follow->user->name ?? 'Deleted user' }}
                            </div>
                        </td>
                        <td>{{ $follow->user->email ?? '—' }}</td>
                        <td>{{ $follow->user->phone ?? '—' }}</td>
                        <td>{{ $follow->user->city ?? '—' }}</td>
                        <td>{{ $follow->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No users are following this seller yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($followers->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $followers->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
