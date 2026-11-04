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

    <div class="col-lg-6 px-0">
        <div class="card">
            <form action="{{ route('admin.settings.order.update') }}" method="POST" class="order-settings-form">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <div class="form-group row align-items-center">
                        <div class="col-md-4">
                            <label class="control-label" for="minimum-order-check">Minimum Order Amount Check</label>
                        </div>
                        <div class="col-md-8">
                            <label class="feature-switch mb-0">
                                <input type="hidden" name="minimum_order_amount_check" value="0">
                                <input id="minimum-order-check" type="checkbox" name="minimum_order_amount_check" value="1" @checked(($settings['minimum_order_amount_check'] ?? '0') === '1')>
                                <span class="feature-switch-track" aria-hidden="true"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-4 d-flex align-items-center">
                            <label class="control-label" for="minimum-order-amount">Set Minimum Order Amount</label>
                        </div>
                        <div class="col-md-8">
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

<style>
    .feature-switch {
        position: relative;
        display: inline-flex;
        width: 48px;
        height: 26px;
        cursor: pointer;
    }

    .feature-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .feature-switch-track {
        position: absolute;
        inset: 0;
        background: #b7c0bd;
        border-radius: 26px;
        transition: background-color .18s ease;
    }

    .feature-switch-track::before {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        content: "";
        background: #fff;
        border-radius: 50%;
        transition: transform .18s ease;
    }

    .feature-switch input:checked + .feature-switch-track {
        background: #267c70;
    }

    .feature-switch input:checked + .feature-switch-track::before {
        transform: translateX(22px);
    }

    .feature-switch input:focus-visible + .feature-switch-track {
        outline: 3px solid rgba(38, 124, 112, .3);
        outline-offset: 2px;
    }
</style>
@endsection