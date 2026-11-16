<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductConversation;
use App\Models\ProductConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductConversationController extends Controller
{
    private const STATUSES = ['open', 'closed'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $conversations = ProductConversation::query()
            ->with(['product:id,name,main_image', 'user:id,name,email', 'seller:id,name'])
            ->withCount('messages')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', '%'.$search.'%')
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return view('admin.support.conversations.index', compact('conversations', 'search', 'status'));
    }

    public function show(ProductConversation $conversation)
    {
        $conversation->load(['product:id,name,main_image', 'user', 'seller', 'messages.sender']);

        $conversation->messages()
            ->whereIn('sender_type', ['customer', 'seller'])
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.support.conversations.show', compact('conversation'));
    }

    public function reply(Request $request, ProductConversation $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        ProductConversationMessage::create([
            'product_conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function updateStatus(Request $request, ProductConversation $conversation)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $conversation->update(['status' => $validated['status']]);

        return back()->with('success', 'Conversation status updated.');
    }

    public function destroy(ProductConversation $conversation)
    {
        $conversation->delete();

        return redirect()->route('admin.support.conversations.index')->with('success', 'Conversation deleted.');
    }
}
