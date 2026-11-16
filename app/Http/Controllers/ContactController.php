<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceivedMail;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Setting;
use App\Models\SupportTicket;
use App\Services\Recaptcha;
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

        if (Recaptcha::enabled('contact') && ! Recaptcha::verify($request, 'contact')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $contact = Contact::create($request->all() + ['user_id' => auth()->id()]);
        $this->createSupportTicket($contact);

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

        if (Recaptcha::enabled('contact') && ! Recaptcha::verify($request, 'contact')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $contact = Contact::create($request->all() + ['user_id' => auth()->id()]);
        $this->createSupportTicket($contact);

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

    private function createSupportTicket(Contact $contact): void
    {
        try {
            SupportTicket::create([
                'user_id' => $contact->user_id,
                'name' => $contact->name,
                'email' => $contact->email,
                'subject' => $contact->subject ?: 'Contact inquiry',
                'category' => 'Contact Form',
                'priority' => 'medium',
                'status' => 'open',
                'message' => $contact->message,
            ]);
        } catch (\Throwable $e) {
            // Never block a contact submission if the support module is unavailable.
            logger()->warning('Support ticket could not be created from contact form: '.$e->getMessage());
        }
    }
}
