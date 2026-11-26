<?php

namespace App\Http\Controllers;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('frontend.checkout.index');
    }
}
