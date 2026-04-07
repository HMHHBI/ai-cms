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
    1. Write a professional reply.
    2. Determine Sentiment (Positive, Negative, or Neutral).
    3. Determine Priority (High, Medium, or Low).
    
    IMPORTANT: You MUST end your response exactly like this:
    Sentiment: [Value]
    Priority: [Value] 
    Do not use bold stars or extra words.";
                   
    try {
      $aiReply = $this->gemini->generateResponse($prompt);

      // --- EXTRACTION LOGIC START ---
      // Hum Regex ke zariye text mein se Sentiment aur Priority nikaal rahe hain
      preg_match('/Sentiment:\s*(\w+)/i', $aiReply, $sentimentMatch);
      preg_match('/Priority:\s*(\w+)/i', $aiReply, $priorityMatch);

      $detectedSentiment = isset($sentimentMatch[1]) ? strtolower($sentimentMatch[1]) : 'neutral';
      $detectedPriority = isset($priorityMatch[1]) ? strtolower($priorityMatch[1]) : 'medium';
      // --- EXTRACTION LOGIC END ---
    } catch (\Exception $e) {
      $aiReply = "AI is currently unavailable. Our team will get back to you soon.";
      $detectedSentiment = 'neutral';
      $detectedPriority = 'medium';
    }

    // 2. Create Ticket in Database
    return Ticket::create([
      'user_id' => $user->id,
      'customer_name' => $data['customer_name'] ?? ($user ? $user->name : 'Guest'), // 👈 Save Name
      'customer_email' => $data['customer_email'] ?? ($user ? $user->email : null),   // 👈 Save Email
      'subject' => $data['subject'],
      'message' => $data['message'],
      'ai_suggestion' => $aiReply,
      'ai_sentiment' => $detectedSentiment,
      'priority' => $detectedPriority,
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