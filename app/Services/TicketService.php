<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketService
{
  protected $gemini;

  public function __construct(GeminiService $gemini)
  {
    $this->gemini = $gemini;
  }

  public function storeTicket(array $data)
  {
    // 1. AI Suggestion & Sentiment Prompt
    $prompt = "Analyze this support ticket: '{$data['message']}'. 
                   Provide a professional reply and include the sentiment 
                   (Positive, Neutral, or Negative) at the end.";
                   
    try {
      $aiReply = $this->gemini->generateResponse($prompt);
    } catch (\Exception $e) {
      $aiReply = "AI is currently unavailable. Our team will get back to you soon.";
    }

    // 2. Create Ticket in Database
    return Ticket::create([
      'user_id' => Auth::id(),
      'subject' => $data['subject'],
      'message' => $data['message'],
      'ai_suggestion' => $aiReply,
      'status' => 'open',
    ]);
  }
}