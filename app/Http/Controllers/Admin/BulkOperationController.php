<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportProductsJob;
use App\Jobs\ImportProductsJob;
use App\Support\BulkOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BulkOperationController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:'.BulkOperation::MAX_UPLOAD_KB,
        ]);

        $file = $request->file('csv_file');
        $id = Str::uuid()->toString();
        Storage::disk('local')->makeDirectory('bulk-imports');
        $path = $file->storeAs('bulk-imports', $id.'.csv', 'local');

        if (! $path) {
            return back()->with('error', 'The CSV could not be stored. Check storage permissions.');
        }

        BulkOperation::init($id, [
            'type' => 'import',
            'status' => 'queued',
            'file_name' => $file->getClientOriginalName(),
            'message' => 'Queued. Waiting for a queue worker...',
        ]);

        ImportProductsJob::dispatch($id, $path, $file->getClientOriginalName());

        return redirect()->route('admin.products.bulk-progress', $id)
            ->with('success', 'Import queued. Track its progress below.');
    }

    public function export()
    {
        $id = Str::uuid()->toString();
        $fileName = 'products-export-'.now()->format('Y-m-d-His').'.csv';

        BulkOperation::init($id, [
            'type' => 'export',
            'status' => 'queued',
            'file_name' => $fileName,
            'message' => 'Queued. Waiting for a queue worker...',
        ]);

        ExportProductsJob::dispatch($id, $fileName);

        return redirect()->route('admin.products.bulk-progress', $id)
            ->with('success', 'Export queued. Track its progress below.');
    }

    public function progress(string $id)
    {
        $state = BulkOperation::get($id);

        if (! $state) {
            abort(404, 'This bulk operation was not found or has expired.');
        }

        return view('admin.products.bulk-progress', [
            'operation' => $state,
            'pollingUrl' => route('admin.products.bulk-progress-json', $id),
        ]);
    }

    public function progressJson(string $id)
    {
        $state = BulkOperation::get($id);

        if (! $state) {
            abort(404);
        }

        return response()->json($state);
    }

    public function download(string $id)
    {
        $state = BulkOperation::get($id);
        $path = $state['export_path'] ?? null;

        if (! $path || ! str_starts_with($path, 'bulk-exports/') || ! Storage::disk('local')->exists($path)) {
            abort(404, 'This export file is no longer available.');
        }

        return Storage::disk('local')->download($path, $state['file_name'] ?? 'products-export.csv');
    }
}
