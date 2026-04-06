<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Ticket;

class PublicTicketController extends Controller
{
    public function show($slug)
    {
        // Slug se company dhoondein taake form par uska naam dikha sakein
        $company = Company::where('slug', $slug)->firstOrFail();

        return view('public.support', compact('company'));
    }

    public function store(Request $request, $slug, \App\Services\TicketService $service)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);

        $aiReply = $service->getAiDraft($validated['message']);

        // Hum TicketService use karenge lekin thori tabdeeli ke saath
        // Kyunki guest user ke paas User ID nahi hoti
        Ticket::create([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'company_id' => $company->id,
            'status' => 'open',
            'ai_suggestion' => $aiReply,
            'customer_name' => $validated['name'], // Ye columns migration mein add karne honge
            'customer_email' => $validated['email'],
            'user_id' => null,
        ]);

        return back()->with('status', 'Your ticket has been submitted! Our team will contact you.');
    }
}
