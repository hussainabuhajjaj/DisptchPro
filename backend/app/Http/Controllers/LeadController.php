<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function create()
    {
        return view('lead');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'company_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'origin' => 'nullable|string',
            'destination' => 'nullable|string',
            'freight_details' => 'nullable|string',
        ]);

        $data['source'] = 'website';
        Lead::create($data);

        return redirect()->back()->with('status', 'Thank you! We received your request.');
    }
}
