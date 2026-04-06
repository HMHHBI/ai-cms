<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Get all tickets for the authenticated user's company.
     */
    public function index()
    {
        $tickets = $this->ticketService->getTicketsForUser(Auth::user());

        return response()->json([
            'status' => 'success',
            'data' => $tickets
        ], 200);
    }

    /**
     * Create a new ticket with AI-powered draft.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $ticket = $this->ticketService->createTicket(Auth::user(), $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket created successfully with AI draft.',
                'data' => $ticket
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }
}