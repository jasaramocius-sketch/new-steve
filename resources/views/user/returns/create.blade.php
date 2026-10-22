@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-return-create-page', 'pageClass' => 'user-return-create-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Request Return - StAutoparts',
        'metaTitle' => 'Request Return | StAutoparts',
        'metaDescription' => 'Request a return for your order at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="user-return-create-page">
<div class="dashboard-topbar">
    <h4 class="h4-style mb-0">Request Return</h4>
    <a href="{{ route('user.returns.index') }}" class="btn btn-outline-primary steve-btn">Back to Returns</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <p class="text-muted">Order <strong>#{{ $order->order_number }}</strong> — {{ $order->created_at->format('d-m-Y') }} — Total {{ currency_format($order->total_amount) }}</p>

        <form action="{{ route('user.returns.store', $order->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table--custom table--responsive-lg">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Ordered</th>
                            <th>Returnable</th>
                            <th>Qty to Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $entry)
                            @php $item = $entry['order_item']; @endphp
                            <tr>
                                <td data-label="Product">{{ $item->product->name ?? 'Product #'.$item->product_id }}</td>
                                <td data-label="Ordered">{{ $item->qty }}</td>
                                <td data-label="Returnable">{{ $entry['available_qty'] }}</td>
                                <td data-label="Qty to Return">
                                    <input type="number" name="items[{{ $loop->index }}][qty]" class="form-control form-control-sm"
                                           min="0" max="{{ $entry['available_qty'] }}" value="0" style="width:90px;">
                                    <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No returnable items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600">Reason for Return <span class="text-danger">*</span></label>
                <textarea name="reason" rows="3" class="form-control" placeholder="Describe the reason (DAMAGED / DEFECTIVE / WRONG ITEM / NOT AS DESCRIBED, etc.)" required>{{ old('reason') }}</textarea>
                @error('reason') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary steve-btn"><i class="fas fa-paper-plane me-2"></i>Submit Return Request</button>
        </form>
    </div>
</div>
</div>
@endsection