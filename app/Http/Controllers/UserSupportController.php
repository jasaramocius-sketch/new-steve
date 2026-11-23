<?php

namespace App\Http\Controllers;

use App\Models\ProductConversation;
use App\Models\ProductConversationMessage;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserSupportController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $conversationsEnabled = FeatureFlags::enabled('conversation_system');

        $tickets = SupportTicket::where('user_id', $userId)
            ->withCount('replies')
            ->latest()
            ->paginate(10, ['*'], 'ticket_page')
            ->withQueryString();

        $conversations = $conversationsEnabled
            ? ProductConversation::where('user_id', $userId)
                ->with(['product:id,name,slug,main_image', 'seller:id,name'])
                ->withCount('messages')
                ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
                ->paginate(10, ['*'], 'conversation_page')
                ->withQueryString()
            : null;

        return view('user.support.index', compact('tickets', 'conversations', 'conversationsEnabled'));
    }

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?: 'General',
            'priority' => $validated['priority'],
            'status' => 'open',
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('user.support.ticket', $ticket)
            ->with('success', 'Your support ticket has been created. Our team will reply soon.');
    }

    public function showTicket(SupportTicket $ticket)
    {
        abort_unless((int) $ticket->user_id === (int) Auth::id(), 404);

        $ticket->load('replies');

        return view('user.support.ticket', compact('ticket'));
    }

    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        abort_unless((int) $ticket->user_id === (int) Auth::id(), 404);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Your reply has been sent.');
    }

    public function showConversation(ProductConversation $conversation)
    {
        abort_unless(FeatureFlags::enabled('conversation_system'), 404);
        abort_unless((int) $conversation->user_id === (int) Auth::id(), 404);

        $conversation->load(['product:id,name,slug,main_image', 'seller:id,name', 'messages']);

        return view('user.support.conversation', compact('conversation'));
    }

    public function replyConversation(Request $request, ProductConversation $conversation)
    {
        abort_unless(FeatureFlags::enabled('conversation_system'), 404);
        abort_unless((int) $conversation->user_id === (int) Auth::id(), 404);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        ProductConversationMessage::create([
            'product_conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        return back()->with('success', 'Your message has been sent.');
    }
}
