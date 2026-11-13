<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email = strtolower(trim($request->email));
        $subscriber = Subscriber::withTrashed()->firstOrCreate(['email' => $email]);
        $exists = ! $subscriber->wasRecentlyCreated && ! $subscriber->trashed();

        if ($subscriber->trashed()) {
            $subscriber->restore();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $exists ? 'You are already subscribed!' : 'Subscribed successfully!',
            ]);
        }

        return back()->with('success', $exists ? 'You are already subscribed!' : 'Subscribed successfully.');
    }
}
