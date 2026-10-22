<div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-600" id="addressModalLabel">Add New Address</h5>
        <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addressForm" method="POST">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="_method" value="POST" id="addressFormMethod">
          @include('partials.address-fields', [
              'prefix' => 'af',
              'withPhone' => true,
              'withDefault' => true,
          ])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary fs-14 fw-600 steve-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fs-14 fw-600 steve-btn" id="addressFormSubmit">Save Address</button>
        </div>
      </form>
    </div>
  </div>
</div>
