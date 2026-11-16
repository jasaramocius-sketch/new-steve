<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    private const STATUSES = ['open', 'pending', 'resolved', 'closed'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $tickets = SupportTicket::query()
            ->withCount('replies')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return view('admin.support.tickets.index', compact('tickets', 'search', 'status'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['replies.sender', 'user']);

        return view('admin.support.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $validated['message'],
        ]);

        $update = ['last_reply_at' => now()];
        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            $update['status'] = 'pending';
        } elseif ($ticket->status === 'open') {
            $update['status'] = 'pending';
        }
        $ticket->update($update);

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('success', 'Ticket status updated.');
    }

    public function destroy(SupportTicket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.support.tickets.index')->with('success', 'Ticket deleted.');
    }
}
