<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingLead;

class LandingPageController extends Controller
{
    public function index() {
        return view('landing');
    }

    public function store(Request $request) {
        $request->validate([
            'email' => 'required|email|unique:landing_leads,email'
        ]);

        LandingLead::create(['email' => $request->email]);

        return back()->with('success', 'Thanks for subscribing!');
    }
}
