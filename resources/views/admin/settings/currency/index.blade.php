@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Currency</span>
        </nav>
        <div class="ap-page-header-row"><h1 class="ap-page-title">Currency</h1></div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">System Default Currency</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.currency.settings') }}" method="POST" class="form-row align-items-end">
                        @csrf
                        <input type="hidden" name="symbol_format" value="{{ $settings['symbol_format'] }}">
                        <input type="hidden" name="decimal_separator" value="{{ $settings['decimal_separator'] }}">
                        <input type="hidden" name="no_of_decimals" value="{{ $settings['no_of_decimals'] }}">
                        <div class="col-md-8 form-group mb-2">
                            <label for="system-default-currency">System Default Currency</label>
                            <select id="system-default-currency" name="system_default_currency" class="form-control" required>
                                @foreach ($activeCurrencies as $currency)
                                    <option value="{{ $currency->code }}" @selected($settings['system_default_currency'] === $currency->code)>{{ $currency->name }} ({{ $currency->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-2"><button class="btn btn-primary" type="submit">Save</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Set Currency Formats</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.currency.settings') }}" method="POST">
                        @csrf
                        <input type="hidden" name="system_default_currency" value="{{ $settings['system_default_currency'] }}">
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="symbol-format">Symbol Format</label>
                            <div class="col-md-8">
                                <select id="symbol-format" name="symbol_format" class="form-control" required>
                                    <option value="1" @selected($settings['symbol_format'] === '1')>[Symbol][Amount]</option>
                                    <option value="2" @selected($settings['symbol_format'] === '2')>[Amount][Symbol]</option>
                                    <option value="3" @selected($settings['symbol_format'] === '3')>[Symbol] [Amount]</option>
                                    <option value="4" @selected($settings['symbol_format'] === '4')>[Amount] [Symbol]</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="decimal-separator">Decimal Separator</label>
                            <div class="col-md-8">
                                <select id="decimal-separator" name="decimal_separator" class="form-control" required>
                                    <option value="1" @selected($settings['decimal_separator'] === '1')>1,23,456.70</option>
                                    <option value="2" @selected($settings['decimal_separator'] === '2')>1.23.456,70</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="no-of-decimals">No of decimals</label>
                            <div class="col-md-8">
                                <select id="no-of-decimals" name="no_of_decimals" class="form-control" required>
                                    @foreach ([0, 1, 2, 3] as $decimals)
                                        <option value="{{ $decimals }}" @selected((int) $settings['no_of_decimals'] === $decimals)>{{ $decimals === 0 ? '12345' : '12345.'.str_repeat('0', $decimals) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right"><button class="btn btn-sm btn-primary" type="submit">Save</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center gutters-5 w-100">
                <div class="col">
                    <h2 class="h6 mb-0">All Currencies</h2>
                    <small class="text-muted">Exchange rates are relative to 1 USD.</small>
                </div>
                <div class="col-auto">
                    <form action="{{ route('admin.settings.currency.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-soft-primary mr-1"><i class="las la-sync" aria-hidden="true"></i> Update rates now</button>
                    </form>
                    <a href="{{ route('admin.settings.currency.create') }}" class="btn btn-sm btn-primary">Add New Currency</a>
                </div>
            </div>
        </div>
        <div class="card-header">
            <form action="{{ route('admin.settings.currency.index') }}" method="GET">
                <div class="row gutters-5 align-items-end">
                    <div class="col-lg-3 col-md-6 col-8 mb-2">
                        <label class="fs-12 text-muted mb-1" for="currency-search">Currency name</label>
                        <input id="currency-search" type="text" class="form-control form-control-sm" name="search" value="{{ $search }}" placeholder="Name or code">
                    </div>
                    <div class="col-auto mb-2"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Currency name</th><th>Currency symbol</th><th>Currency code</th><th>Exchange Rate (1 USD = ?)</th><th>Status</th><th class="text-right">Options</th></tr></thead>
                <tbody>
                    @forelse ($currencies as $currency)
                        <tr>
                            <td>{{ $currencies->firstItem() + $loop->index }}</td>
                            <td>{{ $currency->name }}</td>
                            <td>{{ $currency->symbol }}</td>
                            <td>{{ $currency->code }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $currency->rate, 8, '.', ''), '0'), '.') }}</td>
                            <td>
                                <form action="{{ route('admin.settings.currency.status', $currency) }}" method="POST" class="currency-status-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $currency->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $currency->is_active ? 'Active' : 'Inactive' }}</button>
                                </form>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.settings.currency.edit', $currency) }}" title="Edit {{ $currency->name }}" aria-label="Edit {{ $currency->name }}"><i class="las la-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No currencies found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $currencies->links() }}</div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.currency-status-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: new FormData(form),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Could not update currency.');
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
            button.disabled = false;
        }
    });
});
</script>
@endsection
