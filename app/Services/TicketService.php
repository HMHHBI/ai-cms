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

  public function createTicket($data, $user = null, $companyId = null)
  {
    // 1. Logic: Agar user login hai toh uski company, warna public slug se aayi hui ID
    $finalCompanyId = $user ? $user->company_id : $companyId;
    $finalUserId = $user ? $user->id : null;

    // 2. Advanced Prompt
    $prompt = "Act as a support bot. Analyze: '{$data['message']}'.
    Return ONLY a JSON object with these keys:
    'reply': (professional response),
    'sentiment': (positive/negative/neutral),
    'priority': (high/medium/low)";

    try {
      $aiResponse = $this->gemini->generateResponse($prompt);
      // JSON clean up (kabhi kabhi AI markdown ```json ... ``` bhej deta hai)
      $cleanJson = preg_replace('/```json|```/', '', $aiResponse);
      $result = json_decode($cleanJson, true);

      $aiReply = $result['reply'] ?? "Thanks for contacting us.";
      $detectedSentiment = $result['sentiment'] ?? 'neutral';
      $detectedPriority = $result['priority'] ?? 'medium';
    } catch (\Exception $e) {
      $aiReply = "Our team will assist you shortly.";
      $detectedSentiment = 'neutral';
      $detectedPriority = 'medium';
    }

    return Ticket::create([
      'user_id' => $finalUserId,
      'company_id' => $finalCompanyId,
      'customer_name' => $data['customer_name'] ?? $data['name'] ?? 'Guest',
      'customer_email' => $data['customer_email'] ?? $data['email'] ?? null,
      'subject' => $data['subject'],
      'message' => $data['message'],
      'ai_suggestion' => $aiReply,
      'ai_sentiment' => $detectedSentiment,
      'priority' => $detectedPriority,
      'status' => 'open',
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