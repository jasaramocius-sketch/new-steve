<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceivedMail;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Setting;
use App\Services\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required',
            'message' => 'required',
            'category' => ['nullable', Rule::in(array_keys(Contact::categories()))],
            'priority' => ['nullable', Rule::in(array_keys(Contact::priorities()))],
        ]);

        if (Recaptcha::enabled('contact') && ! Recaptcha::verify($request, 'contact')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $validated['category'] = $validated['category'] ?? 'General';
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['user_id'] = auth()->id();

        $contact = Contact::create($validated);

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

        return view('frontend.pages.contact', compact('page'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required',
            'message' => 'required',
            'category' => ['nullable', Rule::in(array_keys(Contact::categories()))],
            'priority' => ['nullable', Rule::in(array_keys(Contact::priorities()))],
        ]);

        if (Recaptcha::enabled('contact') && ! Recaptcha::verify($request, 'contact')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $validated['category'] = $validated['category'] ?? 'General';
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['user_id'] = auth()->id();

        $contact = Contact::create($validated);

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
        return view('frontend.pages.contact-thank-you');
    }
}
