<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscriber::query();

        if ($search = trim((string) $request->input('email'))) {
            $query->where('email', 'like', '%'.$search.'%');
        }

        $subscribers = $query->latest()->paginate($request->integer('per_page', 20))->withQueryString();

        return view('admin.subscribers.index', compact('subscribers'));
    }

    public function destroy($id)
    {
        $subscriber = Subscriber::findOrFail($id);
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed.');
    }

    public function exportCsv(): StreamedResponse
    {
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'subscribed_at']);

            Subscriber::orderBy('id')->chunk(500, function ($subscribers) use ($handle) {
                foreach ($subscribers as $subscriber) {
                    fputcsv($handle, [
                        $subscriber->email,
                        $subscriber->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}