@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-addresses-page', 'pageClass' => 'user-addresses-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Addresses - StAutoparts',
        'metaTitle' => 'My Addresses | StAutoparts',
        'metaDescription' => 'Manage your shipping addresses at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<style>
    .addreass-page {
    gap: 20px;
    display: flex;
    flex-direction: column;
}
.address-card {
    background: #ffffff;
    padding: 24px;
    border: 1px solid #e9e6e6;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.address-card .address-name {
    font-size: 18px;
    font-weight: 500;
    color: #1f0300;
    margin-bottom: 12px;
}
.address-card .address-detail {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 6px;
    font-size: 15px;
    color: #4c3533;
}
.address-card .address-detail svg {
    flex-shrink: 0;
    margin-top: 2px;
}
.address-card .address-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0eeed;
}
.addreass-page-contener {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}
.addresses-list__plus {
    width: 60px;
    height: 60px;
    position: relative;
    border-radius: 30px;
    margin-bottom: 24px;
    color: #e0e0e0;
    border: 2px solid;
    -webkit-transition: color .2s;
    -moz-transition: color .2s;
    transition: color .2s;
}
.addresses-list__plus:before {
    left: -webkit-calc(50% - 16px);
    left: -moz-calc(50% - 16px);
    left: calc(50% - 16px);
}
.addresses-list__plus:after {
    -webkit-transform: rotate(90deg);
    -moz-transform: rotate(90deg);
    transform: rotate(90deg);
    right: 21%;
}
.addresses-list__plus:after, .addresses-list__plus:before {
    content: "";
    position: absolute;
    width: 32px;
    height: 4px;
    background: currentColor;
    border-radius: 1.5px;
    top: -webkit-calc(50% - 2px);
    top: -moz-calc(50% - 2px);
    top: calc(50% - 2px);
}
.addreass-add-column:hover .addresses-list__plus:after, 
.addreass-add-column:hover .addresses-list__plus:before{
    background-color: var(--primary);
}
 .addreass-add-column:hover .addresses-list__plus{
    color: var(--primary);
}
.addreass-add-column:hover span {
    color: var(--secondary);
}
.addresses-list__plus:after {
    transform: rotate(90deg);
}
.addreass-page-column.addreass-add-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    box-shadow: 0px 0px 3px 0px #0000003b;
}
.addreass-page-column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
}

@media (max-width: 991.98px) {
    .addreass-page-contener {
        grid-template-columns: 1fr;
    }
    .addreass-page-contener .addreass-page-column{
    height: 70vw;
}
}

</style>

<div class="addreass-page">
<h4 class="h4-style mb-0">My Addresses</h4>
<div class="addreass-page-contener">
    <a href="javascript:void(0);"
   class="addreass-page-column addreass-add-column"
   data-bs-toggle="modal"
   data-bs-target="#addAddressModal">

    <div class="addresses-list__plus"></div>
    <span>Add New</span>

</a>    
    @forelse($addresses as $address)
    <div class="address-card addreass-page-column">
        <div class="addreass-page-column-header">
        <div class="address-name">{{ $address->full_name }}</div>
        @if($address->set_default)
            <span class="badge text-success bg-light border border-success-subtle">Default</span>
        @endif
        </div>
        <div class="address-detail">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#796866" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span>{{ $address->address }}, {{ $address->city }}{{ $address->state ? ', ' . $address->state : '' }}, {{ $address->country }} - {{ $address->zip_code }}</span>
        </div>
        <div class="address-detail">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#796866" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span>{{ $address->phone }}</span>
        </div>
        <div class="address-actions action-buttons">
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                <button class="action-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
            </span>
            <form action="{{ route('user.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Remove this address?')" class="d-inline">
                @csrf @method('DELETE')
                <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </form>
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

@empty
<div class="text-center py-5" style="background:#f8f7f7;padding:60px 20px;">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 16px;">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
    </svg>
    <h5 style="margin-bottom:4px;">No addresses yet</h5>
    <p style="color:#8a7b79;margin-bottom:16px;">Add an address to make checkout faster.</p>
    <!-- <button class="steve-btn steve-btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add Address</button> -->
</div>
@endforelse

<!-- Add Address Modal -->
<div class="modal fade address-modal" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('user.addresses.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Address</h5><button class="btn-close steve-btn" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    @include('partials.address-fields', [
                        'prefix' => 'add',
                        'withFullName' => true,
                        'withPhone' => true,
                        'withDefault' => true,
                    ])
                </div>
                <div class="modal-footer">
                    <button class="btn steve-btn steve-btn-sm text-white btn-primary">Save</button>
                    <button class="btn steve-btn steve-btn-sm text-white btn-primary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection