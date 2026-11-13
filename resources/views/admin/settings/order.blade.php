@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-order-settings-page', 'pageClass' => 'admin-order-settings-page'])
@section('page-title', 'Order Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Order Settings',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">

    <div class="col px-0">
        <div class="card">
            <form action="{{ route('admin.settings.order.update') }}" method="POST" class="order-settings-form">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <div class="form-group row align-items-center">
                        <div class="col-md-3">
                            <label class="control-label" for="minimum-order-check">Minimum Order Amount Check</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="minimum_order_amount_check" value="0">
                                <input class="form-check-input" id="minimum-order-check" type="checkbox" role="switch" name="minimum_order_amount_check" value="1" @checked(($settings['minimum_order_amount_check'] ?? '0') === '1')>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3 d-flex align-items-center">
                            <label class="control-label" for="minimum-order-amount">Set Minimum Order Amount</label>
                        </div>
                        <div class="col-md-9">
                            <input id="minimum-order-amount" type="number" min="0" max="10000000" step="0.01" class="form-control" name="minimum_order_amount" value="{{ old('minimum_order_amount', $settings['minimum_order_amount'] ?? '0.00') }}" placeholder="Minimum Order Amount" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1"><i class="fas fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Feature toggles use Bootstrap's shared form-check / form-switch design. --}}
@endsection
