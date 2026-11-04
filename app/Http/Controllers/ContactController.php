<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceivedMail;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $contact = Contact::create($request->all() + ['user_id' => auth()->id()]);

        try {
            Mail::to($contact->email)->send(new ContactReceivedMail($contact, true));
        } catch (\Throwable $e) {
            // Ignore mail failures so the user still sees success
        }

        try {
            $adminEmail = Setting::get('header_email') ?: config('mail.from.address');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactReceivedMail($contact, false));
            }
        } catch (\Throwable $e) {
            // Ignore mail failures so the user still sees success
        }

        return redirect()->route('contact.thank-you');
    }

    public function index()
    {
        $page = Page::where('slug', 'contact')->where('status', true)->first();

        return view('pages.contact', compact('page'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $contact = Contact::create($request->all() + ['user_id' => auth()->id()]);

        try {
            Mail::to($contact->email)->send(new ContactReceivedMail($contact, true));
        } catch (\Throwable $e) {
            // Ignore mail failures so the user still sees success
        }

        try {
            $adminEmail = Setting::get('header_email') ?: config('mail.from.address');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactReceivedMail($contact, false));
            }
        } catch (\Throwable $e) {
            // Ignore mail failures so the user still sees success
        }

        return redirect()->route('contact.thank-you');
    }

    public function thankYou()
    {
        return view('pages.contact-thank-you');
    }
}
