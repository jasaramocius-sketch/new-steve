@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-customers-index-page', 'pageClass' => 'admin-customers-index-page'])
@section('page-title', 'All Customers')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Customers - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <div class=""></div>
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Customer</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @php $customerList = $customers ?? $users ?? collect(); @endphp
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div class="text-muted small">
                Showing {{ $customerList->firstItem() }}-{{ $customerList->lastItem() }} of {{ $customerList->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Name {!! sortIndicator('name', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('email', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Email Address {!! sortIndicator('email', $sortBy, $sortDir) !!}</a></th>
                        <th>Phone</th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('city', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">City {!! sortIndicator('city', $sortBy, $sortDir) !!}</a></th>
                        <th>Followed Sellers</th>
                        <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Joined {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerList as $c)
                    <tr>
                        <td class="ps-3">{{ $customerList->firstItem() + $loop->index }}</td>
                        <td class="user-name-tb">{{ $c->name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->phone ?? '—' }}</td>
                        <td>
                            <form action="{{ route('admin.customers.toggle-status', $c->id) }}" method="POST" class="d-inline featured-status-btn">
                                @csrf
                                <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                    <span class="badge admin-clickable {{ $c->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $c->status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td>{{ $c->city ?? '—' }}</td>
                        <td>
                            @if($c->followedSellers && $c->followedSellers->count())
                                <div class="d-flex flex-wrap gap-1 admin-max-width-220">
                                    @foreach($c->followedSellers->take(3) as $fs)
                                        @if($fs->seller)
                                            <span class="badge bg-light text-dark border">{{ $fs->seller->name }}</span>
                                        @elseif($fs->seller_name)
                                            <span class="badge bg-light text-dark border">{{ $fs->seller_name }}</span>
                                        @endif
                                    @endforeach
                                    @if($c->followedSellers->count() > 3)
                                        <span class="badge bg-secondary">+{{ $c->followedSellers->count() - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $c->created_at->format('d M Y') }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                                <a href="{{ route('admin.customers.show', $c->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                <a href="{{ route('admin.customers.edit', $c->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <form action="{{ route('admin.customers.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $c->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No results found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customerList->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $customerList->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
