@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-configuration-page', 'pageClass' => 'admin-shipping-configuration-page'])
@section('page-title', 'Shipping Configuration')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping Configuration'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    @php
        $shippingMethods = [
            'product_wise_shipping' => 'Product Wise Shipping Cost',
            'flat_rate' => 'Flat Rate Shipping Cost',
            'seller_wise_shipping' => 'Seller Wise Flat Shipping Cost',
            'area_wise_shipping' => 'Area Wise Flat Shipping Cost',
            'carrier_wise_shipping' => 'Carrier Wise Shipping Cost',
        ];
    @endphp

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Select Shipping Method</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="d-flex flex-column align-items-start gap-3">
                        @csrf
                        <input type="hidden" name="type" value="shipping_type">
                        @foreach ($shippingMethods as $value => $label)
                            <div class="form-check mb-3">
                                <input id="shipping-type-{{ $value }}" class="form-check-input" type="radio"
                                       name="shipping_type" value="{{ $value }}"
                                       @checked(old('shipping_type', $shippingType) === $value)>
                                <label class="form-check-label" for="shipping-type-{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Note</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">1. Product Wise Shipping Cost calculation: shipping cost is calculated by adding each product's shipping cost.</li>
                        <li class="list-group-item">2. Flat Rate Shipping Cost calculation: the shipping cost is fixed, regardless of how many products a customer purchases.</li>
                        <li class="list-group-item">3. Seller Wise Flat Shipping Cost calculation: a fixed rate is applied for each seller. Products without a seller use the admin product shipping cost.</li>
                        <li class="list-group-item">4. Area Wise Flat Shipping Cost calculation: a fixed rate is applied for the customer's shipping area. Configure the cost on <a href="{{ route('admin.settings.shipping-cities.index') }}">Shipping Cities</a>, or leave it blank to use the matching <a href="{{ route('admin.settings.shipping-zones.index') }}">Shipping Zone</a> cost.</li>
                        <li class="list-group-item">5. Carrier Wise Shipping Cost calculation: the cost is taken from the active carrier rate for the customer's zone. Configure rates in <a href="{{ route('admin.settings.shipping-zones.index') }}">Shipping Zones</a> and <a href="{{ route('admin.settings.shipping-carriers.index') }}">Shipping Carriers</a>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Flat Rate Cost</h5>
                </div>
                <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="d-flex flex-column align-items-start gap-3">
                    @csrf
                    <input type="hidden" name="type" value="flat_rate_shipping_cost">
                    <div class="card-body d-flex flex-column align-items-start gap-3">
                        <div class="form-group">
                            <label for="flat-rate-shipping-cost">Flat rate shipping cost</label>
                            <input id="flat-rate-shipping-cost" type="number" min="0" step="0.01" name="flat_rate_shipping_cost"
                                   class="form-control" value="{{ old('flat_rate_shipping_cost', $flatRateShippingCost) }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Note</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">1. Flat rate shipping cost is applicable when Flat Rate Shipping is selected above.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Shipping Cost for Admin Products</h5>
                </div>
                <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="d-flex flex-column align-items-start gap-3">
                    @csrf
                    <input type="hidden" name="type" value="shipping_cost_admin">
                    <div class="card-body d-flex flex-column align-items-start gap-3">
                        <div class="form-group">
                            <label for="shipping-cost-admin">Admin product shipping cost</label>
                            <input id="shipping-cost-admin" type="number" min="0" step="0.01" name="shipping_cost_admin"
                                   class="form-control" value="{{ old('shipping_cost_admin', $shippingCostAdmin) }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Note</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">1. Shipping cost for admin products is applicable when Seller Wise Flat Shipping is selected above.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">Home Delivery</h5>
                </div>
                <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="d-flex flex-column align-items-start gap-3">
                    @csrf
                    <input type="hidden" name="type" value="home_delivery_enabled">
                    <div class="card-body flex flex-column align-items-start gap-3">
                        <p class="text-muted">Home delivery is offered when an active shipping method is available for the customer's address and the setting below is enabled.</p>
                        <div class="custom-control custom-checkbox mb-3">
                            <input id="home-delivery-enabled" type="checkbox" name="home_delivery_enabled" value="1"
                                   class="custom-control-input" @checked(old('home_delivery_enabled', $homeDeliveryEnabled))>
                            <label class="custom-control-label" for="home-delivery-enabled">Enable home delivery at checkout</label>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
