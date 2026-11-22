<?php

namespace Liberu\CRM\ServiceAgent\Http\Controllers;


use Illuminate\Routing\Controller;
use Liberu\CRM\ServiceAgent\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required',
        ]);

        QuoteRequest::create($validatedData);

        return redirect()->route('home')->with('success', 'Quote request submitted successfully!');
    }
}
