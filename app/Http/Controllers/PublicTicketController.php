<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Ticket;
use App\Services\TicketService;

class PublicTicketController extends Controller
{
    public function show($slug)
    {
        // Slug se company dhoondein taake form par uska naam dikha sakein
        $company = Company::where('slug', $slug)->firstOrFail();

        return view('public.support', compact('company'));
    }

    public function store(Request $request, $slug, TicketService $service)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);

        // Aik line mein kaam tamam!
        $service->createTicket($validated, null, $company->id);

        return back()->with('status', 'Ticket submitted!');
    }
}
