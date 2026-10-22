@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-profile-page', 'pageClass' => 'user-profile-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Profile - StAutoparts',
        'metaTitle' => 'My Profile | StAutoparts',
        'metaDescription' => 'Manage your account details at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="h4-style mb-1">My Profile</h4>
        <p class="text-muted mb-0">Manage your account details and saved shipping addresses.</p>
    </div>
    <a href="{{ route('user.profile.edit') }}" class="btn btn-primary steve-btn">Update Profile</a>
</div>

<div class="row gy-4 address-page-row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                {!! imgTag(storedPath($profile->avatar), $profile->name, 'rounded-circle mb-3', 'onerror="this.onerror=null;this.src=\''.asset('assets/images/avatar-place.png').'\'' . '"' . ' style="width:120px;height:120px;object-fit:cover;"') !!}
                <h5 class="mb-1">{{ $profile->name }}</h5>
                <p class="text-muted mb-1">{{ $profile->email }}</p>
                <p class="text-muted mb-0">{{ $profile->phone ?? 'No phone number added' }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Basic Info</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>Name:</strong> {{ $profile->name }}</li>
                    <li class="mb-2"><strong>Email:</strong> {{ $profile->email }}</li>
                    <li class="mb-2"><strong>Phone:</strong> {{ $profile->phone ?? 'Not set' }}</li>
                    @php
                        $address = implode(', ', array_filter([
                            $profile->address ?? null,
                            $profile->city ?? null,
                            $profile->country ?? null,
                            $profile->postal_code ?? null,
                        ]));
                    @endphp

                    @if($address)
                        <li class="mb-2">
                            <strong>Address:</strong> {{ $address }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Saved Addresses</h5>
                <!-- <a href="{{ route('user.addresses') }}" class="btn btn-outline-primary btn-sm">Manage Addresses</a> -->
            </div>
        <div class="card-body">
            @if($addresses->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-3">No saved addresses yet.</p>
                    <a href="{{ route('user.addresses') }}" class="btn btn-primary steve-btn">Add Address</a>
                </div>
            @else
                <div class="row g-3">
                    @foreach($addresses as $address)
                        <div class="col-12">
                            <div class="card border rounded-3">
                                <div class="card-body">
                                    @if($address->set_default)
                                        <!-- <span class="badge bg-success mt-2 d-inline-flex align-items-center hart-icon-Default" style="padding: 4px 8px;" title="Default">Default
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                        </span> -->
                                    @endif
                                    <div class="d-flex flex-md-row justify-content-between gap-3 profile-page-saved-addresses">                                            
                                        <div class="address-details">
                                            <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-user-alt me-2 w-fit"></i>
                                            <h6 class="mb-1">{{ $address->full_name }}</h6></div>
                                            <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-map-marker-alt me-2 w-fit"></i>
                                            <p class="mb-1">{{ $address->address }}, {{ $address->city }}, {{ $address->state ? $address->state . ', ' : '' }}{{ $address->country }}, {{ $address->zip_code }}</p></div>
                                            <!-- <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-map-pin-alt me-2 w-fit"></i>
                                            <p class="mb-1">{{ $address->zip_code }}</p></div> -->
                                            <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-phone-alt me-2 w-fit"></i>
                                            <p class="mb-0 text-muted w-fit">{{ $address->phone }}</p>
                                            </div>
                                            @if($address->set_default)
                                            <span class="badge text-success bg-light border border-success-subtle mt-3">Default</span>
                                             @endif

                                    </div>
                                    <div class="address-actions">
                                        <div class="address-actions-dropdown custom-address-dropdown">
                                            <button type="button" class="address-action-toggle custom-address-toggle steve-btn" aria-haspopup="true" aria-expanded="false">&#x22EE;</button>
                                            <div class="address-action-menu custom-address-menu">
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <button type="button" class="action-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </button>
                                                </span>
                                                @if(!$address->set_default)
                                                <form action="{{ route('checkout.address.default', $address->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="action-btn btn-set-default" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Set Default">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('user.addresses.destroy', $address->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this address?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                </div>

                                <!-- Edit Address Modal -->
                                <div class="modal fade address-modal" id="editAddressModal{{ $address->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form action="{{ route('user.addresses.update', $address->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header"><h5 class="modal-title">Edit Address</h5><button class="btn-close steve-btn" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body">
                                                    @include('partials.address-fields', [
                                                        'prefix' => 'edit_' . $address->id,
                                                        'value' => $address,
                                                        'withFullName' => true,
                                                        'withPhone' => true,
                                                        'withDefault' => true,
                                                    ])
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn steve-btn steve-btn-sm text-white btn-primary">Save Changes</button>
                                                    <button class="btn steve-btn steve-btn-sm text-white btn-primary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-4 add-address-btn-container">
                        <a href="{{ route('user.addresses') }}" class="w-100 d-block btn-primary add-address-btn text-white steve-btn">+ Add Address</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function closeAllAddressDropdowns() {
            document.querySelectorAll('.address-actions-dropdown.open').forEach(function(dropdown) {
                dropdown.classList.remove('open');
            });
        }

        document.querySelectorAll('.address-actions-dropdown').forEach(function(dropdown) {
            var toggle = dropdown.querySelector('.address-action-toggle');
            toggle.addEventListener('click', function(event) {
                event.stopPropagation();
                var isOpen = dropdown.classList.contains('open');
                closeAllAddressDropdowns();
                if (!isOpen) {
                    dropdown.classList.add('open');
                }
            });
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.address-actions-dropdown')) {
                closeAllAddressDropdowns();
            }
        });
    });
</script>

@endsection