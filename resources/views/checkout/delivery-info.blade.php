@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'checkout-delivery-info', 'pageClass' => 'checkout-page delivery-step-body bg-light'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Delivery Info - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Delivery Info | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Enter your delivery information at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection
@section('content')

@include('partials.checkout-steps', ['activeStep' => 3])

<section class="py-4">
  <div class="container">
    <div class="row">
      <div class="mx-auto">
        <div class="border bg-white p-4 mb-4">
          <form class="form-default" action="{{ route('checkout.delivery-info.store') }}" role="form" method="POST">
            @csrf

            <div class="card mb-5 border-0 rounded-0 shadow-none">
              <div class="card-header py-3 px-0 border-bottom-0 bg-white">
                <h5 class="fs-16 fw-700 text-dark mb-0">SteveAutoPartsInc. Inhouse Products</h5>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush border p-3 mb-3">
                  @foreach($cart as $item)
                  <li class="list-group-item px-0 px-md-3">
                    <div class="d-flex align-items-center delivery-info-page-products">
                      @php($productUrl = !empty($item['slug']) ? route('product', $item['slug']) : null)
                      @if($productUrl)
                      <a href="{{ $productUrl }}" class="mr-2 mr-md-3 flex-shrink-0 d-inline-block">
                        {!! imgTag(
                          storedPath($item['image'], 'assets/images/thumbnails'),
                          $item['name'],
                          'img-fit size-60px',
                          ''
                        ) !!}
                      </a>
                      <a href="{{ $productUrl }}" class="fs-14 fw-400 text-dark text-decoration-none"><h5 class="mb-0">{{ $item['name'] }}</h5></a>
                      @else
                      <span class="mr-2 mr-md-3 flex-shrink-0">
                        {!! imgTag(
                          storedPath($item['image'], 'assets/images/thumbnails'),
                          $item['name'],
                          'img-fit size-60px',
                          ''
                        ) !!}
                      </span>
                      <span class="fs-14 fw-400 text-dark">
                        <h5 class="mb-0">{{ $item['name'] }}</h5>
                      </span>
                      @endif
                    </div>
                  </li>
                  @endforeach
                </ul>

                <div class="row pt-3">
                  <div class="col-md-6">
                    <h6 class="fs-14 fw-700 mt-3">Choose Delivery Type</h6>
                  </div>
                  <div class="col-md-6">
                    <div class="row gutters-5 justify-content-end">
                      <div class="col-6">
                        <label class="aiz-megabox d-block bg-white mb-0">
                          <input type="radio" name="shipping_method" value="free" checked class="d-none">
                          <span class="d-flex aiz-megabox-elem rounded-0" style="padding: 0.75rem 1.2rem;">
                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                            <span class="flex-grow-1 pl-3 fw-600">Home Delivery</span>
                          </span>
                        </label>
                      </div>
                      @if($pickupEnabled)
                      <div class="col-6">
                        <label class="aiz-megabox d-block bg-white mb-0">
                          <input type="radio" name="shipping_method" value="pickup" class="d-none">
                          <span class="d-flex aiz-megabox-elem rounded-0" style="padding: 0.75rem 1.2rem;">
                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                            <span class="flex-grow-1 pl-3 fw-600">Pickup point</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-12 mt-3 pickup-point-selection" id="pickup-point-selection" hidden>
                        <label for="pickup-point-id" class="fs-14 fw-600">Choose a pickup point</label>
                        <select id="pickup-point-id" name="pickup_point_id" class="form-control">
                          <option value="">Select pickup point</option>
                          @foreach ($pickupPoints as $pickupPoint)
                            <option value="{{ $pickupPoint->id }}">{{ $pickupPoint->name }} — {{ $pickupPoint->location }} ({{ $pickupPoint->contact }})</option>
                          @endforeach
                        </select>
                      </div>
                      @endif
                    </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="pt-4 d-flex justify-content-between align-items-center">
              <a href="{{ route('shop') }}" class="btn-link fw-700 px-0 a-tag-hover-color">
                <i class="fas fa-arrow-left fs-16"></i>
                Return to shop
              </a>
              <button type="submit" class="btn btn-primary fw-700 px-4 steve-btn">Continue to Payment</button>
            </div>
            </div>

            
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script>
const pickupMethodInputs = document.querySelectorAll('input[name="shipping_method"]');
const pickupPointSelection = document.getElementById('pickup-point-selection');
const pickupPointSelect = document.getElementById('pickup-point-id');

function updatePickupPointVisibility() {
  if (!pickupPointSelection || !pickupPointSelect) return;
  const pickupSelected = document.querySelector('input[name="shipping_method"]:checked')?.value === 'pickup';
  pickupPointSelection.hidden = !pickupSelected;
  pickupPointSelect.required = pickupSelected;
  if (!pickupSelected) pickupPointSelect.value = '';
}

pickupMethodInputs.forEach((input) => input.addEventListener('change', updatePickupPointVisibility));
updatePickupPointVisibility();
</script>
@endsection
