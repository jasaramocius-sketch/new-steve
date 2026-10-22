<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Page;
use Illuminate\Http\Request;

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

        Contact::create($request->all() + ['user_id' => auth()->id()]);

        return back()->with('success', 'Message sent successfully.');
    }

    public function index()
    {
        $page = Page::where('slug', 'contact')->where('status', true)->first();

        return view('pages.contact', compact('page'));
    }

    public function store(Request $request)
    {
        Contact::create($request->all() + ['user_id' => auth()->id()]);

        return back()->with('success', 'Message sent successfully');
    }
}
