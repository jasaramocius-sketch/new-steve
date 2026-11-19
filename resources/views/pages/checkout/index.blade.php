@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'checkout-page', 'pageClass' => 'checkout-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Checkout - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Checkout | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Complete your order at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection
@section('content')

@include('partials.checkout-steps', ['activeStep' => 2])

<section class="mb-4">
  <div class="container">
    <div class="row cols-xs-space cols-sm-space cols-md-space">
      <div class="mx-auto">
        <form class="form-default" data-toggle="validator" action="{{ route('checkout.submit') }}" role="form" method="POST">
          @csrf
          <div class="border bg-white p-4 mb-4">
            @foreach($addresses as $address)
            <div class="border mb-4">
              <div class="row">
                <div class="col-md-8">
                  <label class="aiz-megabox d-block bg-white mb-0">                    
                    <span class="d-flex p-3 aiz-megabox-elem border-0">
                      <input type="radio" name="address_id" value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }} required>
                      <span class="flex-grow-1 pl-3 text-left">
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">Address</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->address ?? 'N/A' }}</span>
                        </div>
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">Postal code</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->zip_code ?? 'N/A' }}</span>
                        </div>
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">City</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->city ?? 'N/A' }}</span>
                        </div>
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">State</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->state ?? 'N/A' }}</span>
                        </div>
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">Country</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->country ?? 'N/A' }}</span>
                        </div>
                        <div class="row">
                          <span class="fs-14 text-secondary col-3">Phone</span>
                          <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->phone ?? 'N/A' }}</span>
                        </div>
                      </span>
                    </span>
                  </label>
                </div>
                <div class="col-md-4 p-3 text-end">
                  <a class="btn btn-sm btn-warning text-white mr-4 rounded-0 px-4" onclick="edit_address('{{ $address->id }}')">Change</a>
                </div>
              </div>
            </div>
            @endforeach

            <input type="hidden" name="checkout_type" value="logged">

            @if(isset($vehicles) && $vehicles->count() > 0)
            <div class="border p-3 mb-4" style="border-radius: 8px; background: #f8f9fa;">
              <h6 class="fw-700 mb-3" style="font-size: 14px; color: #333;">
                <i class="las la-car mr-1"></i> Select Vehicle (Optional)
              </h6>
              <p class="text-muted mb-3" style="font-size: 12px;">Tell us which vehicle these parts are for:</p>
              <div class="row g-2">
                @foreach($vehicles as $vehicle)
                <div class="col-md-6 col-lg-4">
                  <label class="aiz-megabox d-block bg-white mb-0" style="border-radius: 6px;">
                    <span class="d-flex p-2 aiz-megabox-elem border-0">
                      <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" {{ $vehicle->id == ($selectedVehicleId ?? null) ? 'checked' : '' }}>
                      <span class="flex-grow-1 pl-2 text-left">
                        <span class="fs-13 text-dark fw-500">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</span>
                        @if($vehicle->engine)
                          <span class="fs-12 text-muted d-block">{{ $vehicle->engine }}</span>
                        @endif
                      </span>
                    </span>
                  </label>
                </div>
                @endforeach
                <div class="col-md-6 col-lg-4">
                  <label class="aiz-megabox d-block bg-white mb-0" style="border-radius: 6px;">
                    <span class="d-flex p-2 aiz-megabox-elem border-0">
                      <input type="radio" name="vehicle_id" value="" {{ $vehicles->isEmpty() || !$selectedVehicleId ? 'checked' : '' }}>
                      <span class="flex-grow-1 pl-2 text-left">
                        <span class="fs-13 text-muted fw-500">Not listed above</span>
                      </span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
            @endif

            <div class="mb-5">
              <div class="border p-3 c-pointer text-center bg-light has-transition hov-bg-soft-light h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                <i class="las la-plus la-2x mb-3"></i>
                <div class="alpha-7 fw-700">Add New Address</div>
              </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
              <a href="{{ route('shop') }}" class="fw-600 a-tag-hover-color">
                <i class="fas fa-arrow-left"></i>
                Return to shop
              </a>
              <button type="submit" class="btn btn-primary fs-14 fw-700 px-4 steve-btn">Continue to Delivery Info</button>
          </div>            
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

@include('partials.address-modal')

@endsection

@section('scripts')
<script>
function add_new_address() {
    $('#addressModalLabel').text('Add New Address');
    $('#addressForm').attr('action', '{{ route("checkout.address.store") }}');
    $('#addressFormMethod').val('POST');
    $('#addressForm')[0].reset();
    $('#addressFormSubmit').text('Save Address');
    $('#addressModal').modal('show');
}

function edit_address(id) {
    $.get('{{ url("/checkout/address") }}/' + id + '/edit', function(data) {
        $('#addressModalLabel').text('Edit Address');
        $('#addressForm').attr('action', '{{ url("/checkout/address") }}/' + id);
        $('#addressFormMethod').val('PUT');
        $('#af_phone').val(data.phone);
        $('#af_address').val(data.address);
        var countrySel = $('#af_country');
        if (countrySel.length && countrySel.find('option[value="' + data.country + '"]').length === 0) {
            countrySel.append($('<option>', { value: data.country, text: data.country }));
        }
        countrySel.val(data.country);
        // Use a native change event so address-fields' addEventListener
        // handler runs (jQuery .trigger() only fires jQuery-bound handlers).
        if (countrySel[0]) countrySel[0].dispatchEvent(new Event('change', { bubbles: true }));
        var stateSel = $('#af_state');
        stateSel.attr('data-selected', data.state || '');
        if (data.state && stateSel.find('option[value="' + data.state + '"]').length === 0) {
            stateSel.append($('<option>', { value: data.state, text: data.state }));
        }
        stateSel.val(data.state);
        if (stateSel[0]) stateSel[0].dispatchEvent(new Event('change', { bubbles: true }));
        var citySel = $('#af_city');
        // Set data-selected BEFORE the async cities fetch resolves so the
        // cascade's fillSelect preserves the saved city value.
        citySel.attr('data-selected', data.city || '');
        if (data.city && citySel.find('option[value="' + data.city + '"]').length === 0) {
            citySel.append($('<option>', { value: data.city, text: data.city }));
        }
        citySel.val(data.city);
        if (citySel[0]) citySel[0].dispatchEvent(new Event('change', { bubbles: true }));
        $('#af_zip').val(data.zip_code);
        $('#af_set_default').prop('checked', !!data.set_default);
        $('#addressFormSubmit').text('Update Address');
        $('#addressModal').modal('show');
    });
}

$(document).ready(function() {
    function validateAddressForm(form) {
        var fields = [
            { name: 'phone', label: 'Phone', regex: /^[0-9+\-\s()]{7,20}$/ },
            { name: 'country', label: 'Country', regex: /.+/ },
            { name: 'state', label: 'State', regex: /.+/, required: false },
            { name: 'city', label: 'City', regex: /.+/ },
            { name: 'zip_code', label: 'Zip Code', regex: /^[A-Za-z0-9\-\s]{3,20}$/ },
            { name: 'address', label: 'Address', regex: /\S/ }
        ];

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var input = form.querySelector('[name="' + field.name + '"]');
            if (!input) continue;

            var value = (input.value || '').trim();
            var isRequired = input.required || (field.required !== false && field.name !== 'state');

            // Focus the visible searchable-select trigger (the real field is hidden).
            var focusInput = function() {
                var w = input.closest ? input.closest('.address-suggest') : null;
                var t = w && w.querySelector('.address-suggest-trigger');
                if (t) t.focus(); else input.focus();
            };

            if (isRequired && value === '') {
                if (window.toastr) {
                    toastr.warning(field.label + ' is required.');
                } else {
                    alert(field.label + ' is required.');
                }
                focusInput();
                return false;
            }

            if (value && field.regex && !field.regex.test(value)) {
                if (window.toastr) {
                    toastr.warning('Please enter a valid ' + field.label + '.');
                } else {
                    alert('Please enter a valid ' + field.label + '.');
                }
                focusInput();
                return false;
            }
        }

        return true;
    }

    $('#addressForm').data('addressAjax', '1')[0].dataset.addressAjax = '1';

    $('#addressForm').on('submit', function(e) {
        var form = this;
        form.dataset.addressAjax = '1';

        if (!validateAddressForm(form)) {
            e.preventDefault();
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            e.preventDefault();
            return;
        }

        e.preventDefault();
        var url = $(form).attr('action');
        var method = $('#addressFormMethod').val();
        var data = $(form).serialize();

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#addressModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                    var msg = '';
                    $.each(errors, function(key, val) {
                        msg += (Array.isArray(val) ? val.join('\n') : val) + '\n';
                    });

                    if (msg) {
                        if (window.toastr) {
                            toastr.error(msg.trim());
                        } else {
                            alert(msg.trim());
                        }
                    } else if (window.toastr) {
                        toastr.error('Please correct the highlighted fields.');
                    } else {
                        alert('Please correct the highlighted fields.');
                    }

                    var firstField = Object.keys(errors)[0];
                    if (firstField) {
                        var firstInput = form.querySelector('[name="' + firstField + '"]');
                        if (firstInput) {
                            var focusEl = firstInput;
                            var fw = firstInput.closest ? firstInput.closest('.address-suggest') : null;
                            var ft = fw && fw.querySelector('.address-suggest-trigger');
                            if (ft) focusEl = ft;
                            focusEl.focus();
                            focusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                } else {
                    alert('Something went wrong!');
                }
            }
        });
    });
});
</script>
@endsection