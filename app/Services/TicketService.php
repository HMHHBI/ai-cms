<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
  protected $gemini;

  public function __construct(GeminiService $gemini)
  {
    $this->gemini = $gemini;
  }

  /**
   * Get tickets for a specific user.
   */
  public function getTicketsForUser($user)
  {
    if ($user->role === 'admin') {
      // Admin apni POORI company ke tickets dekhe ga
      return Ticket::where('company_id', $user->company_id)->latest()->get();
    }

    // Staff sirf wo tickets dekhay ga jo:
    // 1. Usne khud banaye hon (Existing logic)
    // 2. YA usay assign kiye gaye hon (New logic)
    return Ticket::where('company_id', $user->company_id)
      ->where(function ($query) use ($user) {
        $query->where('user_id', $user->id)
          ->orWhere('assigned_to', $user->id)
          ->orWhereNull('assigned_to');
      })
      ->latest()
      ->get();
  }

  public function createTicket($user, array $data)
  {
    // 1. AI Suggestion & Sentiment Prompt
    $prompt = "Analyze this support ticket: '{$data['message']}'. 
    Write a professional reply. 
    At the very end, you MUST write exactly in this format: 
    [SENTIMENT: NEGATIVE] or [SENTIMENT: POSITIVE] or [SENTIMENT: NEUTRAL]. 
    Do not use bold stars or extra words.";
                   
    try {
      $aiReply = $this->gemini->generateResponse($prompt);
    } catch (\Exception $e) {
      $aiReply = "AI is currently unavailable. Our team will get back to you soon.";
    }

    // 2. Create Ticket in Database
    return Ticket::create([
      'user_id' => $user->id,
      'subject' => $data['subject'],
      'message' => $data['message'],
      'ai_suggestion' => $aiReply,
      'status' => 'open',
      'company_id' => $user->company_id,
    ]);
  }

  // TicketService.php ke andar add karein

  public function getAiDraft($message)
  {
    $prompt = "Analyze this support ticket: '{$message}'. 
               Provide a professional reply and include the sentiment 
               (Positive, Neutral, or Negative) at the end.";

    try {
      return $this->gemini->generateResponse($prompt);
    } catch (\Exception $e) {
      return "AI is currently unavailable. Our team will get back to you soon.";
    }
  }
}