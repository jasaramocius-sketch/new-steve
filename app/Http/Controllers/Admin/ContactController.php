<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Mail\ContactReplyMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'subject', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 20;

        $query = Contact::with('product');

        if ($source = $request->query('source')) {
            if ($source === 'product') {
                $query->whereNotNull('product_id');
            } elseif ($source === 'contact') {
                $query->whereNull('product_id');
            }
        }

        $contacts = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        $contacts->appends($request->query())->onEachSide(1);

        return view('admin.contacts.index', compact('contacts', 'sortBy', 'sortDir'));
    }

    public function show($id)
    {
        $contact = Contact::with('product', 'user', 'replier')->findOrFail($id);

        return view('admin.contacts.show', compact('contact'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $contact = Contact::findOrFail($id);

        $contact->update([
            'reply' => trim($request->reply),
            'replied_by' => auth()->id(),
            'replied_at' => now(),
        ]);

        // Notify the user (in-app bell) whenever a reply is saved on their inquiry.
        if ($contact->user_id) {
            NotificationHelper::inquiryReplied($contact);
        }

        if ($contact->email) {
            Mail::to($contact->email)->send(new ContactReplyMail($contact->load('product')));
        }

        return redirect()->route('admin.contacts.show', $contact->id)
            ->with('success', 'Reply saved successfully.');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return back()->with('success', 'Contact deleted successfully.');
    }
}
